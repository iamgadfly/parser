<?php

namespace App\Http\Controllers;

use App\Actions\PriceDeliveryAction;
use App\Repositories\ProductRepository;
use App\Services\ParserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ParserController extends Controller
{
    public static function index(ParserService $parserService, ProductRepository $productRepository)
    {
        return $parserService->parseByLinks($productRepository);
    }

    public function parseByOneId(Request $request, ParserService $parserService, $product_id, $is_command = true)
    {
       return $parserService->parseByLink($request->product_id);
    }
}
