<?php

namespace App\Repositories;

use App\Models\PostMeta;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Calculation\Database\DVar;
use PhpParser\Node\Expr\Cast\Object_;

class ProductRepository
{

    public function getByBackMarketId(string $back_market_id):array
    {
         $raw_data = DB::select(DB::raw("SELECT post_id, meta_key, meta_value FROM wp_postmeta WHERE post_id = ( SELECT post_id FROM wp_postmeta WHERE meta_key = 'backmarket_id' and meta_value='$back_market_id' limit 1);"));
         foreach ($raw_data as $value){
             $product[$value->meta_key] = $value->meta_value;
             $post_id = $value->post_id;
         }
        $product['post_id'] = $post_id;
         return $product;
    }

    public function getAllProducts(): array
    {
        $raw_data = DB::select(DB::raw(
            "SELECT post_id, t.name AS product_category, IF(p.post_parent = 0, p.ID, p.post_parent) AS post_parent, (SELECT post_title FROM wp_posts WHERE id = pm.post_id) AS title, (SELECT meta_value FROM wp_postmeta WHERE post_id = pm.post_id AND meta_key = '_price' LIMIT 1) AS price,  (SELECT meta_value FROM wp_postmeta WHERE post_id = pm.post_id AND meta_key = '_regular_price' LIMIT 1) AS 'regular price',  (SELECT meta_value FROM wp_postmeta WHERE post_id = pm.post_id AND meta_key = '_stock' LIMIT 1) AS stock,  (SELECT meta_value FROM wp_postmeta WHERE post_id = pm.post_id AND meta_key = '_stock_status' LIMIT 1) AS 'stock status',  IFNULL((SELECT meta_value FROM wp_postmeta WHERE post_id = pm.post_id AND meta_key = 'backmarket_id' LIMIT 1), (SELECT meta_value FROM wp_postmeta WHERE post_id = pm.post_id AND meta_key = 'backmarket_id' LIMIT 1)) as backmarket_id, IFNULL((SELECT meta_value FROM wp_postmeta WHERE post_id = pm.post_id AND meta_key = 'attribute_pa_sostoyanie' LIMIT 1), (SELECT meta_value FROM wp_postmeta WHERE post_id = pm.post_id AND meta_key = 'attribute_pa_sostoyanie' LIMIT 1)) as state, IFNULL(SUBSTR( (SELECT meta_value FROM wp_postmeta WHERE post_id = pm.post_id AND meta_key = '_product_attributes' LIMIT 1), INSTR((SELECT meta_value FROM wp_postmeta WHERE post_id = pm.post_id AND meta_key = '_product_attributes' LIMIT 1), 'is_variation')+16,1),1) AS 'variation'  FROM `wp_postmeta` AS pm JOIN wp_posts AS p ON p.ID = pm.post_id JOIN wp_term_relationships AS tr ON tr.object_id = IF(p.post_parent = 0, p.ID, p.post_parent) JOIN wp_term_taxonomy AS tt ON tt.taxonomy = 'product_cat' AND tt.term_taxonomy_id = tr.term_taxonomy_id  JOIN wp_terms AS t ON t.term_id = tt.term_id  WHERE meta_key in ('_product_version') AND p.post_status in ('publish') AND IFNULL(SUBSTR((SELECT meta_value FROM wp_postmeta WHERE post_id = pm.post_id AND meta_key = '_product_attributes' LIMIT 1), INSTR((SELECT meta_value FROM wp_postmeta WHERE post_id = pm.post_id AND meta_key = '_product_attributes' LIMIT 1), 'is_variation')+16,1),0)=0;"
        ));
        return $raw_data;
    }

    public function updateParserData(string $value, $post_id, $meta_key): void
    {
         DB::table('wp_postmeta')->update(['meta_value' => $value])->where([
            'post_id' => $post_id,
            'meta_key' => $meta_key,
        ]);
    }
}
