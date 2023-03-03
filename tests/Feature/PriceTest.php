<?php

namespace Tests\Feature;

use App\Actions\PriceDeliveryAction;
use App\Http\Controllers\ParserController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PriceTest extends TestCase
{
    public function test_price_is_int_then_price_is_new_samsung()
    {
        $response = $this->post('/api/test_price', [
        'object_id' => 3364,
        'raw_price' => 499,
        ]);

        $response->assertStatus(200);
    }

    public function test_price_is_int_then_price_is_new_iphone()
    {
        $response = $this->post('/api/test_price', [
        'object_id' => 12102,
        'raw_price' => 1399,
        ]);

        $response->assertStatus(200);
    }

    public function test_price_is_int_then_price_is_new_ipad()
    {
        $response = $this->post('/api/test_price', [
        'object_id' => 11537,
        'raw_price' => 479,
        ]);

        $response->assertStatus(200);
    }

    public function test_price_is_int_then_price_is_new_imac()
    {
        $response = $this->post('/api/test_price', [
        'object_id' => 11521,
        'raw_price' => 999,
        ]);

        $response->assertStatus(200);
    }

    public function test_price_is_int_then_price_is_new_macbook()
    {
        $response = $this->post('/api/test_price', [
        'object_id' => 11373,
        'raw_price' => 1200,
        ]);

        $response->assertStatus(200);
    }
}
