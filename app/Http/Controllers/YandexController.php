<?php

namespace App\Http\Controllers;

use App\Repositories\ProductRepository;
use App\Services\YandexService;
use Illuminate\Http\Request;

class YandexController extends Controller
{
    public function accept(Request $request, YandexService $yandexService)
    {
        return $yandexService->aboutProduct($request->all()['order']);
    }
}
