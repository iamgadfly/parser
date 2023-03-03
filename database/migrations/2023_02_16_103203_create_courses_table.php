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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 5,2);
            $table->boolean('is_auto')->default(false);
            $table->timestamps();
        });

        DB::table('courses')->insert([
            'name' => 'Доллар',
            'price' => 74,
        ]);

        DB::table('courses')->insert([
            'name' => 'Shopfans',
            'price' => 83,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('courses');
    }
};
