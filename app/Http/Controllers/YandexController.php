<?php

namespace App\Http\Controllers;

use App\Services\YandexService;
use Illuminate\Http\Request;

class YandexController extends Controller
{
    public function accept(Request $request, YandexService $yandexService)
    {
        if (empty($request->all()['order']) || !isset($request->all()['order'])) {
            return response()->json(['Error' => 'Data is invalid'], 400);
        }
        return $yandexService->aboutProduct($request->all()['order']);
    }
}
