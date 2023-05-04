<?php

namespace App\Services;

use App\Services\TranslateService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class RebagService
{
    public function __construct(
        protected TranslateService $translateService,
    ) {}

    public function index($req_data)
    {
        dd(json_decode($this->getApiRebag()));
        dd($req_data);
    }

    public function getApiRebag()
    {
        //return Http::get('https://api.rebag.com/api/v6/shop/product/?collection_scope=0&page=1&pf_t_bag_size_hidden%5B%5D=meta-size-mm&pf_t_categories%5B%5D=bc-filter-Bags&pf_t_color%5B%5D=bc-filter-exterior-color-Brown&pf_t_first_look_hidden%5B%5D=bc-filter-General%20View&pf_t_first_look_hidden%5B%5D=bc-filter-extra-tags-First%20Look&pf_t_meta_model_hidden%5B%5D=meta-model-neverfull-tote&pf_t_meta_style_hidden%5B%5D=meta-style-damier&pf_v_designers%5B%5D=Louis%20Vuitton&q%5B%5D=Louis%20Vuitton&sort=created-descending&sort_first=available');
        //? LV example
        // return Http::get('https://api.rebag.com/api/v6/shop/product/?&sort_first=available&pf_v_designers%5B%5D=Louis%20Vuitton&q%5B%5D=Louis%20Vuitton&page=2');
        // sort_first=available&pf_v_designers%5B%5D=Louis%20Vuitton&q%5B%5D=Louis%20Vuitton
        // %5B%5D
        // $brands = ['pf_v_designers=Louis%20Vuitton&q%5B%5D=Louis%20Vuitton', 'pf_v_designers=Chanel', 'pf_v_designers=Chloe', 'pf_v_designers=Bottega%20Veneta', 'pf_v_designers=Balenciaga'];
        // $brands = ['pf_v_designers=Louis%20Vuitton&q%5B%5D=Louis%20Vuitton'];

        // https://api.rebag.com/api/v6/shop/product/?collection_scope=17987005&page=1&pf_t_first_look_hidden%5B%5D=bc-filter-General%20View&pf_t_price%5B%5D=bc-filter-%24500%20to%20%241%E2%80%9A500&pf_t_price%5B%5D=bc-filter-%24100%20to%20%24500&sort=created-descending&sort_first=available
        // // foreach ($brands as $brand) {
        // for ($i = 1; $i < 300; $i++) {
        //     // $parsed_data = json_decode(Http::get("https://api.rebag.com/api/v6/shop/product/?&sort_first=available&$brand&page=$i"));
        //     // if (empty($parsed_data->products)) {
        //     //     break;
        //     // }
        //     // $products[] = $parsed_data->products;
        //     // Http::get("https://api.rebag.com/api/v6/shop/product/?&sort_first=available&$brand&page=$i"
        //     $check = Http::get("https://api.rebag.com/api/v6/shop/product/?&sort_first=available&pf_v_designers=Chloe&page=$i&pf_t_price%5B%5D=bc-filter-%24500%20to%20%241%E2%80%9A500&pf_t_price%5B%5D=bc-filter-%24100%20to%20%24500");
        //     $parsed = json_decode($check);
        //     if (!empty($parsed->products)) {
        //         fwrite($fp, $check);
        //     } else {
        //         dd(4322);
        //     }
        // }
        // $check = Http::get("https://api.rebag.com/api/v6/shop/product/?&sort_first=available&pf_t_first_look_hidden%5B%5D=bc-filter-General%20View&pf_v_designers%5B%5D=Louis%20Vuitton&page=1&pf_t_price%5B%5D=bc-filter-%24500%20to%20%241%E2%80%9A500&pf_t_price%5B%5D=bc-filter-%24100%20to%20%24500");
        $check = Http::get("https://api.rebag.com/api/v6/shop/product/?collection_scope=0&page=1&pf_t_first_look_hidden%5B%5D=bc-filter-General%20View&pf_v_designers%5B%5D=Louis%20Vuitton&sort=created-descending&sort_first=available&pf_t_price%5B%5D=bc-filter-%24500%20to%20%241%E2%80%9A500&pf_t_price%5B%5D=bc-filter-%24100%20to%20%24500"); // ! LV parse
        //! все нужные бренды
        //? https://api.rebag.com/api/v6/shop/product/?collection_scope=0&page=1&pf_t_first_look_hidden%5B%5D=bc-filter-General%20View&pf_v_designers%5B%5D=Louis%20Vuitton&sort=created-descending&sort_first=available&pf_t_price%5B%5D=bc-filter-%24500%20to%20%241%E2%80%9A500&pf_t_price%5B%5D=bc-filter-%24100%20to%20%24500
        //? Chanel https://api.rebag.com/api/v6/shop/product/?collection_scope=0&page=1&pf_t_first_look_hidden%5B%5D=bc-filter-General%20View&pf_v_designers%5B%5D=Chanel&sort=created-descending&sort_first=available&pf_t_price%5B%5D=bc-filter-%24500%20to%20%241%E2%80%9A500&pf_t_price%5B%5D=bc-filter-%24100%20to%20%24500
        //? Chlode https://api.rebag.com/api/v6/shop/product/?collection_scope=0&page=1&pf_t_first_look_hidden%5B%5D=bc-filter-General%20View&pf_v_designers%5B%5D=Chloe&sort=created-descending&sort_first=available&pf_t_price%5B%5D=bc-filter-%24500%20to%20%241%E2%80%9A500&pf_t_price%5B%5D=bc-filter-%24100%20to%20%24500
        //? Balenciaga https://api.rebag.com/api/v6/shop/product/?collection_scope=0&page=1&pf_t_first_look_hidden%5B%5D=bc-filter-General%20View&pf_v_designers%5B%5D=Balenciaga&sort=created-descending&sort_first=available&pf_t_price%5B%5D=bc-filter-%24500%20to%20%241%E2%80%9A500&pf_t_price%5B%5D=bc-filter-%24100%20to%20%24500
        //? Bottega https://api.rebag.com/api/v6/shop/product/?collection_scope=0&page=1&pf_t_first_look_hidden%5B%5D=bc-filter-General%20View&pf_v_designers%5B%5D=Bottega%20Veneta&sort=created-descending&sort_first=available&pf_t_price%5B%5D=bc-filter-%24500%20to%20%241%E2%80%9A500&pf_t_price%5B%5D=bc-filter-%24100%20to%20%24500
        // dd(json_decode($check));

        // $brands = [
        // 'https://api.rebag.com/api/v6/shop/product/?collection_scope=0&page=1&pf_t_first_look_hidden%5B%5D=bc-filter-General%20View&pf_v_designers%5B%5D=Louis%20Vuitton&sort=created-descending&sort_first=available&pf_t_price%5B%5D=bc-filter-%24500%20to%20%241%E2%80%9A500&pf_t_price%5B%5D=bc-filter-%24100%20to%20%24500',
        // ];
// ADD to urls to get all bags &pf_t_categories%5B%5D=bc-filter-Bags
        // https://api.rebag.com/api/v6/shop/product/?collection_scope=0&page=$i&pf_t_first_look_hidden%5B%5D=bc-filter-General%20View&pf_v_designers%5B%5D=Bottega%20Veneta&sort=created-descending&sort_first=available&pf_t_categories%5B%5D=bc-filter-Bags&pf_t_price%5B%5D=bc-filter-%24500%20to%20%241%E2%80%9A500&pf_t_price%5B%5D=bc-filter-%24100%20to%20%24500
        // https://api.rebag.com/api/v6/shop/product/?collection_scope=0&page=$i&pf_t_first_look_hidden%5B%5D=bc-filter-General%20View&pf_v_designers%5B%5D=Louis%20Vuitton&sort=created-descending&sort_first=available&pf_t_price%5B%5D=bc-filter-%24500%20to%20%241%E2%80%9A500&pf_t_price%5B%5D=bc-filter-%24100%20to%20%24500
	//$jwt_token = $this->authToken();
        $fp = fopen(Storage::path('bottega.log'), 'a');
        $i = 1;
        do {
            $check = Http::get("https://api.rebag.com/api/v6/shop/product/?collection_scope=0&page=$i&pf_t_first_look_hidden%5B%5D=bc-filter-General%20View&pf_v_designers%5B%5D=Louis%20Vuitton&sort=created-descending&sort_first=available&pf_t_price%5B%5D=bc-filter-%24500%20to%20%241%E2%80%9A500&pf_t_price%5B%5D=bc-filter-%24100%20to%20%24500");
            $parsed = json_decode($check);
            if (!empty($parsed->products)) {
                foreach ($parsed->products as $key => $product) {
                    // dd($product->body_html);
                    $raw_data_state = explode('Condition:', $product->body_html);
                    // $raw_data_desc = explode('Accessories:', $product->body_html);
                    $raw_sizes = explode(',', str_replace('"', '', stristr(trim(explode('Measurements:', $product->body_html)[1]), 'Designer', true)));
                    $raw_materials = explode('Material:', $product->body_html);
                    unset($raw_materials[0]);
                    foreach ($raw_materials as $material) {
                        $materials[] = stristr(trim($material), 'Color:', true);
                    }

                    foreach ($raw_sizes as $size) {
                        $sizes[preg_replace('/[^a-zA-Z]/', '', $size)] = preg_replace("/[^,.0-9-]/", '', $size);
                    }

                    // $meterial = explode('Material:', $product->body_html)[1];
                    // dd($meterial);

                    // $desc = stristr(trim($raw_data_desc[1]), 'Interior Color:', true);

                    // $state = stristr('.', $raw_data[2], true);
                    // $state = substr(strpos($raw_data[2], "."), 0, 1);
                    // $state = substr(strpos($raw_data[2], '.'), 1, strlen($raw_data[2]));
                    // dd($state);
                    $data = [
                        'state'     => ctype_digit($product->metafields[2]->value) ? stristr(trim($raw_data_state[1]), '.', true) : $product->metafields[2]->value,
                        // ?? str_replace('.', '', stristr(trim($raw_data[2]), ' ', true)),
                        // str_replace('.', '', stristr(trim($raw_data[2]), ' ', true)),
                        'regular_price' => "$product->price_min_usd",
                        'name'      => $product->title,
			'product_type' => 	   'simple',
                        // $product->variants[0]->title,
                        'rebag_id'  => $product->variants[0]->id,
                        'images'    => $product->images,
                        'brand'     => $product->vendor,
                        'color'     => str_replace('color:', '', $product->variants[0]->merged_options[1]),
                        'dimensions'     => array_change_key_case($sizes),
                        'materials' => $materials,
                        // 'desc'      => trim($desc),
                    ];
					$translate_data = $this->translateService->translate([$data['color'], array_values($data['materials'])]);
					$data = array_replace($data, $translate_data);
					foreach($data['images'] as $image){
							$images[]['src'] = $image; 
					}
					$data['images'] = $images;
					$data['categories'] = match($data['brand']){
						'Louis Vuitton' => [['id' => 464], ['id' => 469]],
					};
					//dd($data);
		    $wp_product = $this->createProductWP($data);
		    dd($wp_product);
                    logger('botega_data_test', json_decode($data['images']));
                }
            }
            return 322;
            $i++;
        } while (!empty($parsed->products));

        fclose($fp);
        return 322;
    }
    
public function createProductWP($data) {
	$curl = curl_init();
	curl_setopt_array($curl, array(
CURLOPT_URL => 'https://recommerce-dev.ru/wp-json/wc/v3/products?consumer_key=ck_6d4c35ca173023bbbc1a48bd17e7b54d96e995b3&consumer_secret=cs_22bfcdefeb71ac4d0ad36668de5ef65c958bdb05',
CURLOPT_RETURNTRANSFER => true,
CURLOPT_ENCODING => '',
CURLOPT_MAXREDIRS => 10,
CURLOPT_TIMEOUT => 0,
CURLOPT_FOLLOWLOCATION => true,
CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
CURLOPT_CUSTOMREQUEST => 'POST',
CURLOPT_POSTFIELDS => json_encode($data),
CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
),
));
	    $response = curl_exec($curl);

	    curl_close($curl);
	    return json_decode($response);
}
}
