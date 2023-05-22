<?php

namespace App\Services;

use App\Actions\PriceDeliveryRebagAction;
use App\Repositories\ProductRepository;
use App\Services\TranslateService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RebagService
{
    public function __construct(
        protected TranslateService $translateService,
        protected ProductRepository $productRepository,
    ) {}

    public function index($req_data)
    {
        dd(json_decode($this->getApiRebag()));
    }

    public function getApiRebag()
    {
        $i = 1;
        $atributes = collect(json_decode(Http::get(env('WP_URL', 'https://recommerce-dev.ru/') . '/wp-json/wc/v3/products/attributes?consumer_key=' . env('WP_KEY', 'ck_ed0bd9742aa86ec2583160e7420f1f485cb4ea70') . '&' . 'consumer_secret=' . env('WP_SECRET', 'cs_90575e933df47298b06da8156007da72b120e7d8'))));
        $wp_aitributes = $atributes->whereIn('name', ['Цвет', 'Состояние', 'Размер', 'Бренд'])->pluck('id', 'slug');
        $categories = collect(json_decode(Http::get(env('WP_URL', 'https://recommerce-dev.ru/') . '/wp-json/wc/v3/products/categories?per_page=30&consumer_key=' . env('WP_KEY', 'ck_ed0bd9742aa86ec2583160e7420f1f485cb4ea70') . '&' . 'consumer_secret=' . env('WP_SECRET', 'cs_90575e933df47298b06da8156007da72b120e7d8'))));
        $wp_categories = $categories->whereIn('slug', ['sumki', 'louis-vuitton', 'chanel', 'chloe', 'balenciaga', 'bottega'])->pluck('id', 'slug');
        $dollar_course = $this->productRepository->getCourseByName('Доллар');
        do {
            $links = [
                "&pf_t_first_look_hidden%5B%5D=bc-filter-General%20View&pf_v_designers%5B%5D=Louis%20Vuitton&sort=created-descending&sort_first=available&pf_t_price%5B%5D=bc-filter-%24500%20to%20%241%E2%80%9A500&pf_t_price%5B%5D=bc-filter-%24100%20to%20%24500",
                "&pf_t_first_look_hidden%5B%5D=bc-filter-General%20View&pf_v_designers%5B%5D=Chanel&sort=created-descending&sort_first=available&pf_t_price%5B%5D=bc-filter-%24500%20to%20%241%E2%80%9A500&pf_t_price%5B%5D=bc-filter-%24100%20to%20%24500",
                "&pf_t_first_look_hidden%5B%5D=bc-filter-General%20View&pf_v_designers%5B%5D=Chloe&sort=created-descending&sort_first=available&pf_t_price%5B%5D=bc-filter-%24500%20to%20%241%E2%80%9A500&pf_t_price%5B%5D=bc-filter-%24100%20to%20%24500",
                "&pf_t_first_look_hidden%5B%5D=bc-filter-General%20View&pf_v_designers%5B%5D=Balenciaga&sort=created-descending&sort_first=available&pf_t_price%5B%5D=bc-filter-%24500%20to%20%241%E2%80%9A500&pf_t_price%5B%5D=bc-filter-%24100%20to%20%24500",
                "&pf_t_first_look_hidden%5B%5D=bc-filter-General%20View&pf_v_designers%5B%5D=Bottega%20Veneta&sort=created-descending&sort_first=available&pf_t_price%5B%5D=bc-filter-%24500%20to%20%241%E2%80%9A500&pf_t_price%5B%5D=bc-filter-%24100%20to%20%24500",
            ];
            foreach ($links as $key => $link) {
                $check = Http::get("https://api.rebag.com/api/v6/shop/product/?collection_scope=0&page=" . $i . $link);
                $parsed = json_decode($check);
                if (!empty($parsed->products)) {
                    foreach ($parsed->products as $key => $product) {
                        $check_product = DB::table('wp_posts')->where('post_title', $product->title)->first();
                        if (!empty($check_product)) {
                            //dd($check_product);
                            continue;
                        }

                        $raw_data_state = explode('Condition:', $product->body_html);
                        $raw_sizes = explode(',', str_replace('"', '', stristr(trim(explode('Measurements:', $product->body_html)[1]), 'Designer', true)));
                        $raw_materials = explode('Material:', $product->body_html);
                        unset($raw_materials[0]);
                        foreach ($raw_materials as $material) {
                            $materials[] = stristr(trim($material), 'Color:', true);
                        }

                        foreach ($raw_sizes as $size) {
                            $sizes[preg_replace('/[^a-zA-Z]/', '', $size)] = preg_replace("/[^,.0-9-]/", '', $size);
                        }

                        $create_data = $this->getProductData($raw_data_state, $product, $dollar_course, $sizes, $materials);
                        $create_data = array_replace($create_data, $this->translateService->translate([$create_data['color'], array_values($create_data['materials'])]));
                        // $create_data = array_replace($create_data, $translate_data);

                        foreach ($create_data['images'] as $image) {
                            $images[]['src'] = $image;
                        }
                        $create_data['images'] = $images;
                        $create_data['categories'] = [
                            ['id' => $wp_categories['sumki']],
                            ['id' => $create_data['brand'] == 'Louis Vuitton' ? $wp_categories['louis-vuitton'] : $wp_categories[mb_strtolower($create_data['brand'])]],
                        ];

                        $wp_product = $this->createProductWP($create_data);
						if(is_null($wp_product){
						continue;
						}
                        $variation = $this->createVariationWP($wp_product->id, [
                            'regular_price' => $create_data['regular_price'],
                            '_sale_pice'    => $create_data['regular_price'],
                            'rebag_id'      => $product->variants[0]->id,
                            'attributes'    => [
                                [
                                    'id'     => 37,
                                    'option' => $product->vendor,
                                ],
                            ],
                        ]);
                        DB::transaction(function () use ($variation, $product, $create_data) {
                            $this->productRepository->createRebagPostMeta($variation, $product, $create_data);
                        });

                        dd($variation);
                    }

                } else {
                    $i = 1;
                }
                $i++;
            }
        } while (!empty($parsed->products));

        //   DB::transaction(function()  {
        DB::select(DB::raw("
						DELETE t1 FROM wp_postmeta t1
						INNER JOIN wp_postmeta t2
						WHERE  t1.meta_id < t2.meta_id
						AND  t1.meta_key = t2.meta_key
						AND t1.post_id=t2.post_id;")
        );
        //   });
    }

    public function createVariationWP($product_id, $data)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            // /wp-jsvariationon/wc/v3/products/$product_id/variations
            CURLOPT_URL            => env('WP_URL') . "/wp-json/wc/v3/products/$product_id/variations?consumer_key=ck_ed0bd9742aa86ec2583160e7420f1f485cb4ea70&consumer_secret=cs_90575e933df47298b06da8156007da72b120e7d8",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
            ),
        ));
        $response = curl_exec($curl);

        curl_close($curl);
        //dd($response);
        return json_decode($response);
    }

    public function createProductWP($data)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
//CURLOPT_URL => env('WP_URL') . '/wp-json/wc/v3/products?consumer_key=ck_ed0bd9742aa86ec2583160e7420f1f485cb4ea70&consumer_secret=cs_90575e933df47298b06da8156007da72b120e7d8',
            CURLOPT_URL            => env('WP_URL') . '/wp-json/wc/v3/products?consumer_key=ck_ed0bd9742aa86ec2583160e7420f1f485cb4ea70&consumer_secret=cs_90575e933df47298b06da8156007da72b120e7d8',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
            ),
        ));
        $response = curl_exec($curl);

        curl_close($curl);
        //dd($response);
        return json_decode($response);
    }

    public function getProductData($raw_data_state, $product, $dollar_course, $sizes, $materials): array
    {
        $delivery = ((int) $product->price_min_usd > 500) ? 40 : 60;

        return [
            'state'         => $this->getState(stristr(trim($raw_data_state[1]), '.', true)),
            'regular_price' => PriceDeliveryRebagAction::priceCalculate($product->price_min_usd, $dollar_course, $delivery),
            'name'          => $product->title,
            'product_type'  => 'simple', // simple
            // $product->variants[0]->title,
            'rebag_id' => $product->variants[0]->id,
            'images'        => $product->images,
            'brand'         => $product->vendor,
            'color'         => str_replace('color:', '', $product->variants[0]->merged_options[1]),
            'dimensions'    => array_change_key_case($sizes),
            'materials'     => $materials,
            // 'desc'      => trim($desc),
        ];
    }

    public function getState($state)
    {
        return match ($state) {
            'Pristine', 'Excellent' => 'kak-novyj',
            'Great', 'Very good' => 'otlichnoe',
            'Good', 'Fair'       => 'horoshee',
            default                 => '-',
        };
    }
}
