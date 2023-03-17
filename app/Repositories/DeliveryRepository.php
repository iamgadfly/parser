<?php

namespace App\Repositories;

use App\Models\PostMeta;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Calculation\Database\DVar;
use PhpParser\Node\Expr\Cast\Object_;

class DeliveryRepository
{
    public function updateDelivery($delivery_ids, $prices)
    {
        DB::update("UPDATE deliveries SET price = CASE $prices END WHERE id IN ($delivery_ids);");
    }
}
