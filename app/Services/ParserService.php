<?php

namespace App\Services;

use App\Actions\PriceDeliveryAction;
use App\Enums\Constants;
use App\Enums\CourseNames;
use App\Repositories\ProductRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ParserService
{
    public function __construct(
        protected ProductRepository $productRepository,
    ) {}

    public function parseByLinks(): void
    {
        $products = $this->productRepository->getAllProducts();
        $productsChunks = collect($products)->unique('post_id')->chunk(50);
        $dollar_course = $this->productRepository->getCourseByName(CourseNames::DOLLAR);
        $snopfan_course = $this->productRepository->getCourseByName(CourseNames::SHOPFANS);

        foreach ($productsChunks as $products) {
            $this->getDataForProduct($products, $dollar_course, $snopfan_course);
        }
        logger('test_parsing', ['success']);
    }

    public function getDataForProduct($products, $dollar_course, $snopfan_course)
    {
        try {
            foreach ($products as $product) {
                if (empty($product->backmarket_id) || is_null($product->backmarket_id) || $product->backmarket_id == '' || is_null($product->state)) {
                    logger('bug_empty_url (backmarket_id)', [$product]);
                    continue;
                }

                $product_parsed_data_state = $this->getDataState($this->getApiBackmarket($product->backmarket_id));
                $data_state = $this->getApiBackmarket($product->backmarket_id, false);
                $parsed_data = $this->getDataFromParsedData($product, $data_state, $product_parsed_data_state);
                if (is_null($parsed_data['states'])) {
                    logger('parse_error / Backmarket return null', ['post_id' => $product->post_id]);
                    continue;
                }
                $state_data = match ($product->state) {
                    'horoshee' => $parsed_data['states'][0] ?? null,
                    'otlichnoe' => $parsed_data['states'][1] ?? null,
                    'kak-novyj' => $parsed_data['states'][2] ?? null,
                };
                if (!is_null($state_data['price']) && !is_null($product->regular_price) && !is_null($state_data) && isset($state_data['price'])) {
                    $weight = PriceDeliveryAction::getWeightByCategory($product->product_category);
                    $delivery = PriceDeliveryAction::getDeliveryByWeightAndPrice($weight, $state_data['price']) ?? null;
                    if (is_null($delivery)) {
                        logger('bug', ['weight' => $weight, 'price' => $state_data['price'], 'product' => $product]);
                        continue;
                    }
                    $customs_comisson = PriceDeliveryAction::getCustomsCommissionsByWeightAndPrice($weight, $state_data['price']);
                    if (is_null($customs_comisson)) {
                        logger('bug customs_comisson cant be null', ['weight' => $weight, 'price' => $state_data['price']]);
                        continue;
                    }
                    $price = PriceDeliveryAction::priceCalculate($weight, $state_data['price'], $dollar_course, $delivery, $snopfan_course, $customs_comisson, Constants::AGENT_COMMISSION, Constants::PAYMENT_COMMISSION) ?? null;
                    $stock = $this->getStock($state_data['in_stock']);
                    $count = $this->getCount($state_data);
                    $change = $product->regular_price - $price;
                    $common_price = PriceDeliveryAction::priceRound(($change > 5000) ? $product->regular_price : $price + rand(5000, 10000), 50);
                } else if (!is_null($product->regular_price) && is_null($state_data['price']) && !is_null($state_data['price'] && !is_null($product->price))) {
                    $stock = 'outofstock';
                    $count = 0;
                    $price = PriceDeliveryAction::priceRound($product->regular_price, 50);
                    //if($price < $product->price){
                    //}
                    //$product->regular_price + rand(5000, 10000);
                } else {
                    logger('bug empty regular_price and state price = NULL', ['prod' => $product, 'state' => $state_data]);
                    continue;
                }
                if (!is_null($state_data['price'])) {
			$price_usd = $state_data['price']; 
				//"($product->post_id, 'price_usd', " . $state_data['price'] . ')';
		    $price_logistic = PriceDeliveryAction::getPriceLogistic($weight, $state_data['price'], $dollar_course, $delivery, $snopfan_course, $customs_comisson,Constants::PAYMENT_COMMISSION); 
			    //"($product->post_id, 'price_logistic', " . PriceDeliveryAction::getPriceLogistic($weight, $state_data['price'], $dollar_course, $delivery, $snopfan_course, $customs_comisson,Constants::PAYMENT_COMMISSION) . ')';
                }

                $query_usd_price[] = $price_usd;
                $query_price_logistic[] = $price_logistic;

                $post_ids[] = $product->post_id;
                $query_price[] = $price;
                $query_common_price[] = !isset($common_price) ? $product->regular_price : $common_price;
                $query_status[] = "WHEN post_id = $product->post_id THEN '$stock'";
                $query_value[] = "WHEN post_id = $product->post_id THEN '$count'";
                if (is_null($state_data)) {
                    $state_data = [];
                }
                $this->writeLog($state_data, $product->backmarket_id);

                $check_product[$product->post_parent][$product->post_id] = $stock;
                //}
            }
            if (!empty($parent)) {
                $parent = $this->updateProductParent($check_product);
                $parent_status = implode(' ', array_values($parent));
                $parent_ids = implode(', ', array_keys($parent));

                $this->productRepository->updateStockStatus($parent_ids, $parent_status, '_stock_status');
            }
            //  $links_query = implode(' ', $links);
            $query_sale_price = implode(', ', $query_price);
            //insertBackMarketUrl
            $query_usd_price = implode(', ', $query_usd_price);

            $query_logistic_price = implode(', ', $query_price_logistic);
            $query_common_price = implode(', ', $query_common_price);

            $query_stat = implode(' ', $query_status);
            $query_stat_stock = implode(' ', $query_value);
            $product_ids = implode(', ', $post_ids);

            DB::transaction(function () use ($product_ids, $query_sale_price, $query_common_price, $query_stat, $query_stat_stock, $query_usd_price, $query_logistic_price) {
                //$this->productRepository->updateStockStatus($parent_ids, $parent_status, '_stock_status');
                $this->productRepository->updatePrice($product_ids, $query_sale_price, '_sale_price');
                $this->productRepository->updatePrice($product_ids, $query_common_price, '_price');

		$this->productRepository->updatePrice($product_ids, $query_usd_price, 'price_usd');
		$this->productRepository->updatePrice($product_ids, $query_logistic_price, 'price_logistic');

                $this->productRepository->updateStockStatus($product_ids, $query_stat, '_stock_status');
                $this->productRepository->updateStockStatus($product_ids, $query_stat_stock, '_stock');
                //        $productRepository->updateStockStatus($product_ids, $links_query, 'backmarket_url');
            });
        } catch (\Exception $e) {
            logger('error', [$e]);
        }
    }

    public function parseByLink($product_id, ProductRepository $productRepository, PriceDeliveryAction $action): bool
    {
        $product = $productRepository->getByBackMarketId($product_id);
        $product_parsed_data_state = $this->getDataState($this->getApiBackmarket($product_id));
        $parsed_data = $this->getDataFromParsedData($product, $this->getApiBackmarket($product_id, false), $product_parsed_data_state);

        $product = $productRepository->getOneProduct($product_id);
        $product = $product[0] ?? null;
        $product_state = substr($product->post_name, strrpos($product->post_name, '-') + 1);
        $state_data = match ($product_state) {
            'horoshee' => $parsed_data['states'][0],
            'otlichnoe' => $parsed_data['states'][1],
            'kak-novyj' => $parsed_data['states'][2],
        };
        $stock = $this->getStock($state_data['in_stock']);
        $count = $this->getCount($state_data);

        $post_parent_id = DB::table('wp_posts')->select('post_parent')->where('ID', $product->post_id)->first();
        $price = $action($post_parent_id->post_parent, $state_data['price']);

        $productRepository->updateParserData($price, $product->post_id, '_sale_price');
        $productRepository->updateParserData($stock, $product->post_id, '_stock_status');
        $productRepository->updateParserData($count, $product->post_id, '_stock');

        dd('Продукт успешно обнолвен');
    }

    public function getDataState(array $state_data): array | null
    {
        foreach ($state_data as $value) {
            if (is_array($value)) {
                if (isset($value['backboxGrade']) || isset($value['isDisabled']) || isset($value['price'])) {
                    $data_state[] = [
                        'price'    => $value['price']['amount'] ?? null,
                        'state'    => $value['backboxGrade']['name'],
                        'value'    => $value['backboxGrade']['value'],
                        'in_stock' => $value['isDisabled'],
                    ];
                }
            }
        }
        return $data_state ?? null;
    }

    public function updateProductParent(array $states): array
    {
        foreach ($states as $key => $val) {
            if (in_array('instock', $states[$key])) {
                $data[$key] = "WHEN post_id = $key THEN 'instock'";
            } else {
                $data[$key] = "WHEN post_id = $key THEN 'outofstock'";
            }
        }

        return $data;
    }

    public function getState($sost, $parsed_data): array
    {
        return match ($sost) {
            'horoshee' => $parsed_data['states'][0],
            'otlichnoe' => $parsed_data['states'][1],
            'kak-novyj' => $parsed_data['states'][2],
        };
    }

    public function getStock($stock)
    {
        return match ($stock) {
            true => 'outofstock',
            false => 'instock',
        };
    }

    public function getCount($state_data): int
    {
        if ($state_data['in_stock'] === false || $state_data['in_stock'] === true && isset($state_data['stock'])) {
            $count = $state_data['value'];
        } else if ($state_data['in_stock'] === false && !isset($state_data['stock'])) {
            $count = 10;
        } else {
            $count = 0;
        }
        return $count;
    }

    public function getApiBackMarket(string $product_id, bool $is_state = true): array | false
    {
        $url = match ($is_state) {
            true => "https://www.backmarket.com/bm/product/$product_id/v3/best_offers",
            false => "https://www.backmarket.com/bm/product/v2/$product_id",
        };

        $response = Http::get($url) ?? false;
        if ($response === false) {
            return false;
        }

        return json_decode($response->body(), true);
    }

    public function writeLog(array $data, string $product_id): void
    {
        $fp = fopen(Storage::path('parser/parse.log'), 'a');
        if (!empty($data)) {
            $text = " товар с ID $product_id спаршен";
        } else {
            $text = " товар с ID $product_id не спаршен";
        }

        fwrite($fp, Carbon::now()->format('d-m-Y H:m:s') . $text . PHP_EOL);
        fclose($fp);
    }

    public function getDataFromParsedData($product, $data_product, $data_state): array
    {
        return [
            'states' => $data_state ?? null,
        ];
    }}
