<?php

use App\Http\Controllers\Auth\LoginController as AuthLoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController as ControllersLoginController;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session as FacadesSession;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Row;
use Orchid\Platform\Http\Controllers\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::middleware(['auth'])->group(static function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/chnage_course/{name}', [HomeController::class, 'getChaneCoursePage']);
    Route::post('/chnage_course/{name}', [HomeController::class, 'saveCourse'])->name('change_course');
    Route::get('/logout', [HomeController::class, 'lophpgout'])->name('logout');
    Route::get('/update_product', [HomeController::class, 'getUpdateProductView']);
    Route::post('/update_product', [HomeController::class, 'updateProduct']);
    Route::post('/add_job', [HomeController::class, 'addJob']);

    Route::post('/save_deliveries', [HomeController::class, 'saveDiliveries']);
    Route::get('/deliveries', [HomeController::class, 'getDeliveriesView']);
});


Route::get('/login', [ControllersLoginController::class, 'index']);
Route::post('/login', [ControllersLoginController::class, 'login'])->name('login');
// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
