<?php

namespace App\Console\Commands;

use App\Repositories\ProductRepository;
use Illuminate\Console\Command;
use App\Services\ParserService;
use Illuminate\Support\Facades\DB;

class InsertBrandCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert_brand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
     
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(ProductRepository $productRepository, ParserService $parserService)
    {
	$products = $productRepository->getAllProducts();
	$products = collect($products)->unique('post_parent');
	foreach ($products as $key => $product) {
		if(is_null($product->backmarket_id)){
		continue;
		}
		//dd($product);
		$data_state = $parserService->getApiBackmarket($product->backmarket_id, false);
		if(isset($data_state['trackingDetails']['brand'])){

$url = "https://recommerce-dev.ru/wp-json/wc/v3/products/$product->post_parent";

//$consumer_key = 'ck_6cd3d2320dd9e8ccb52efda34fdc134ce17f58b9';
//$consumer_secret = 'cs_82799b312141fab490feed182974eda91b587b0e';

$consumer_key = 'ck_ed0bd9742aa86ec2583160e7420f1f485cb4ea70';
$consumer_secret = 'cs_90575e933df47298b06da8156007da72b120e7d8';

$headers = array(
    'Authorization' => 'Basic ' . base64_encode($consumer_key.':'.$consumer_secret )
);

// получаем текущие атрибуты
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_TIMEOUT, 30);

curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
//curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
//for debug only!
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_USERPWD, "$consumer_key:$consumer_secret");
$resp = curl_exec($curl);
$status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE); 
curl_close($curl);

$resp_data = json_decode($resp, true);

// получаем текущие атрибуты
$attributes['attributes'] = $resp_data['attributes'];

$brand = [
    'id' => '37', // id атрибута бренда в админке
    'position' => 0,
    'variation' => false,
    'options' => $data_state['trackingDetails']['brand'],
];

// добавляем бренд
array_push($attributes['attributes'], $brand);

// обновляем товар с вставкой бренда и других атрибутов
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_TIMEOUT, 30);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT"); //обновляем

curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

// готовим данные атрибутов
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($attributes));

//for debug only!
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_USERPWD, "$consumer_key:$consumer_secret");
$resp = curl_exec($curl);
$status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE); 
curl_close($curl);
		}
        }
	dd('успешно');
    }
    
    public static function getAtributes($product_id)
    {
	    $headers = array(
    'Authorization' => 'Basic ' . base64_encode('ck_ed0bd9742aa86ec2583160e7420f1f485cb4ea70'.':'. 'cs_90575e933df47298b06da8156007da72b120e7d8')
	    );

	$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, "https://recommerce-dev.ru/wp-json/wc/v3/products/$product_id",
);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_TIMEOUT, 30);

curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
//curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
//for debug only!
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_USERPWD, "ck_ed0bd9742aa86ec2583160e7420f1f485cb4ea70:cs_90575e933df47298b06da8156007da72b120e7d8");
$resp = curl_exec($curl);
$status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE); 
curl_close($curl);
return json_decode($resp, true);

    }
    public static function curl($id, $data)
    {
    	$curl = curl_init();
	curl_setopt_array($curl, array(
	CURLOPT_URL => "https://recommerce-dev.ru/wp-json/wc/v3/products/$id?consumer_key=ck_ed0bd9742aa86ec2583160e7420f1f485cb4ea70&consumer_secret=cs_90575e933df47298b06da8156007da72b120e7d8",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => '',
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 0,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => 'PUT',
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
