<?php

namespace App\Providers;

use App\Actions\PriceDeliveryAction;
use App\Repositories\DeliveryRepository;
use App\Repositories\ProductRepository;
use App\Services\ParserService;
use App\Services\RebagService;
use App\Services\TranslateService;
use App\Services\YandexService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(PriceDeliveryAction::class, function () {
            return new PriceDeliveryAction();
        });

        $this->app->bind(ParserService::class, function () {
            return new ParserService(new ProductRepository());
        });

        $this->app->bind(RebagService::class, function () {
            return new RebagService(new TranslateService(), new ProductRepository());
        });

        $this->app->bind(ProductRepository::class, function () {
            return new ProductRepository();
        });

        $this->app->bind(DeliveryRepository::class, function () {
            return new ProductRepository();
        });

        $this->app->bind(YandexService::class, function () {
            return new YandexService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
