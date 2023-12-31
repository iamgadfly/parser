<?php

namespace App\Repositories;

use App\Models\PostMeta;
use Illuminate\Support\Facades\DB;

class ProductRepository
{

    public function getByBackMarketId(string $back_market_id): array
    {
        $raw_data = DB::select(DB::raw("SELECT post_id, meta_key, meta_value FROM wp_postmeta WHERE post_id = ( SELECT post_id FROM wp_postmeta WHERE meta_key = 'backmarket_id' and meta_value='$back_market_id' limit 1);"));
        return self::convertedData($raw_data);
    }

    public function getAllProducts(): array
    {
        //       $mysqli = new \mysqli(env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'));
        //$mysqli = new \mysqli('localhost', 'fagosejz_cdek', '123QwertY!', 'fagosejz_cdek');
        $sql = "SELECT post_id, t.name AS product_category, IF(p.post_parent = 0, p.ID, p.post_parent) AS post_parent, (SELECT meta_value FROM wp_postmeta WHERE post_id = pm.post_id AND meta_key = '_regular_price' LIMIT 1) AS 'regular_price', (SELECT meta_value FROM wp_postmeta WHERE post_id = pm.post_id AND meta_key = '_price' LIMIT 1) AS 'price',  (SELECT meta_value FROM wp_postmeta WHERE post_id = pm.post_id AND meta_key = '_stock_status' LIMIT 1) AS 'stock status',  IFNULL((SELECT meta_value FROM wp_postmeta WHERE post_id = pm.post_id AND meta_key = 'backmarket_id' LIMIT 1), (SELECT meta_value FROM wp_postmeta WHERE post_id = pm.post_id AND meta_key = 'backmarket_id' LIMIT 1)) as backmarket_id, IFNULL((SELECT meta_value FROM wp_postmeta WHERE post_id = pm.post_id AND meta_key = 'attribute_pa_sostoyanie' LIMIT 1), (SELECT meta_value FROM wp_postmeta WHERE post_id = pm.post_id AND meta_key = 'attribute_pa_sostoyanie' LIMIT 1)) as state, IFNULL(SUBSTR( (SELECT meta_value FROM wp_postmeta WHERE post_id = pm.post_id AND meta_key = '_product_attributes' LIMIT 1), INSTR((SELECT meta_value FROM wp_postmeta WHERE post_id = pm.post_id AND meta_key = '_product_attributes' LIMIT 1), 'is_variation')+16,1),1) AS 'variation'  FROM `wp_postmeta` AS pm JOIN wp_posts AS p ON p.ID = pm.post_id JOIN wp_term_relationships AS tr ON tr.object_id = IF(p.post_parent = 0, p.ID, p.post_parent) JOIN wp_term_taxonomy AS tt ON tt.taxonomy = 'product_cat' AND tt.term_taxonomy_id = tr.term_taxonomy_id  JOIN wp_terms AS t ON t.term_id = tt.term_id  WHERE meta_key in ('_product_version') AND p.post_status in ('publish') AND IFNULL(SUBSTR((SELECT meta_value FROM wp_postmeta WHERE post_id = pm.post_id AND meta_key = '_product_attributes' LIMIT 1), INSTR((SELECT meta_value FROM wp_postmeta WHERE post_id = pm.post_id AND meta_key = '_product_attributes' LIMIT 1), 'is_variation')+16,1),0)=0;";
        $raw_data = DB::select(DB::raw($sql));
        //    $raw_data = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
        return $raw_data;
    }

    public function updateParserData(string $value, $post_id, $meta_key): void
    {
        DB::table('wp_postmeta')->where([
            'post_id'  => $post_id,
            'meta_key' => $meta_key,
        ])->update(['meta_value' => $value]);
    }

    public function getOneProduct($product_id)
    {
        return DB::select(DB::raw("SELECT * FROM wp_posts p JOIN wp_postmeta pm1 ON ( pm1.post_id = p.ID) WHERE p.post_type in('product', 'product_variation') AND p.post_status = 'publish' and pm1.meta_value = '$product_id' LIMIT 1"));
    }

    public function getProductByIds($product_ids): array
    {
        $raw_data = DB::select(DB::raw(" select post_id, meta_key, meta_value from wp_postmeta where post_id IN ($product_ids) and (meta_key='_stock_status' OR meta_key='_sale_price');"));
        $raw_data = array_chunk($raw_data, ceil(count($raw_data) / count(explode(',', $product_ids))));
        foreach ($raw_data as $value) {
            $products[] = self::convertedData($value);
        }
        return $products;
    }

    public function updatePrice($product_ids, $query_sale_price, $key)
    {
        DB::select(DB::raw("UPDATE `wp_postmeta` SET meta_value = ELT(FIELD(post_id, $product_ids), $query_sale_price) WHERE post_id IN ($product_ids) and meta_key='$key';"));
    }

    public function updateStockStatus($product_ids, $query_stat, $meta_key)
    {
        DB::update("UPDATE wp_postmeta SET meta_value = CASE $query_stat END WHERE post_id IN ($product_ids) and meta_key='$meta_key'");
    }

    public function getProductById($id): array
    {
        $raw_data = DB::select(DB::raw("SELECT post_id, meta_key, meta_value FROM wp_postmeta WHERE post_id = $id;"));
        return self::convertedData($raw_data);
    }

    public function insertBackMarketUrl($insert)
    {
        DB::select(DB::raw("REPLACE INTO wp_postmeta (post_id, meta_key, meta_value) VALUES $insert AS new ON DUPLICATE KEY UPDATE meta_key = new.meta_key AND meta_value = new.meta_value;"));
    }

    public function getCourseByName($name): int
    {
        return DB::table('courses')->where('name', $name)->first()->price;
    }

    public static function convertedData($raw_data): array
    {
        foreach ($raw_data as $value) {
            $product[$value->meta_key] = $value->meta_value;
            $post_id = $value->post_id;
        }
        $product['post_id'] = $post_id;
        return $product;
    }

    public function createRebagPostMeta($variation, $product, $create_data): void
    {
        PostMeta::updateOrCreate(
            [
                'post_id'    => $variation->id,
                'meta_key'   => 'rebag_id',
                'meta_value' => $product->variants[0]->id,
            ],
            [
                'meta_key'   => 'rebag_id',
                'meta_value' => $product->variants[0]->id,
            ]);

        PostMeta::updateOrCreate(
            [
                'post_id'    => $variation->id,
                'meta_key'   => 'attribute_pa_sostoyanie',
                'meta_value' => $create_data['state'],
            ],
            [
                'meta_key'   => 'attribute_pa_sostoyanie',
                'meta_value' => $create_data['state'],
            ]);

        PostMeta::updateOrCreate(
            [
                'post_id'    => $variation->id,
                'meta_key'   => '_sale_price',
                'meta_value' => $create_data['regular_price'],
            ],
            [
                'meta_key'   => '_sale_price',
                'meta_value' => $create_data['regular_price'],
            ]);

    }
}
