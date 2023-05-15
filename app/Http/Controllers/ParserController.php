<?php

namespace App\Http\Controllers;

use App\Services\ParserService;
use App\Services\RebagService;
use Illuminate\Http\Request;

class ParserController extends Controller
{
    public static function index(ParserService $parserService)
    {
        return $parserService->parseByLinks();
    }

    public static function parseByOneId(Request $request, ParserService $parserService, $product_id, $is_command = true)
    {
        return $parserService->parseByLink($request->product_id);
    }

    public static function rebag(Request $request, RebagService $rebagService)
    {
        return $rebagService->index($request->collect());
    }
}
