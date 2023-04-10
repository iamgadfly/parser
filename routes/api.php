<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ParserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/parse', [ParserController::class, 'index']);

// Route::get('/test', function(){
// dd(DB::table('wp_postmeta')->first());
// });

Route::post('/test_price', [HomeController::class, 'test']);
