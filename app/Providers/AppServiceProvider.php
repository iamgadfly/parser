<?php

namespace App\Providers;

use App\Actions\PriceDeliveryAction;
use App\Repositories\DeliveryRepository;
use App\Repositories\DeliveryRepositoryRepository;
use App\Repositories\ProductRepository;
use App\Services\ParserService;
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
        $this->app->bind(PriceDeliveryAction::class, function (){
            return new PriceDeliveryAction();
        });

        $this->app->bind(ParserService::class, function (){
            return new ParserService();
        });

        $this->app->bind(ProductRepository::class, function (){
            return new ProductRepository();
        });

        $this->app->bind(DeliveryRepository::class, function (){
            return new ProductRepository();
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
