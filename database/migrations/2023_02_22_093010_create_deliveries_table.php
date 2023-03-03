<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->float('weight');
            $table->decimal('price', 5,2);
        });

        $weights = [1, 1.5, 3.5, 15];
        foreach($weights as $weight){
            $price_onex = match($weight){
                1 => 21,
                1.5 => 35,
                3.5 => 77,
                15 => 224,
            };
            $price_snopfans = match($weight){
                1 => 30.49,
                1.5 => 33.99,
                3.5 => 112.99,
                15 => 399.49,
            };
            DB::table('deliveries')->insert([
                'name' => 'Shopfans',
                'weight' => $weight,
                'price' => $price_snopfans,
            ]);
            DB::table('deliveries')->insert([
                'name' => 'Onex',
                'weight' => $weight,
                'price' => $price_onex,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deliveries');
    }
};
