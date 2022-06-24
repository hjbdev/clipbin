<?php

namespace App\Providers;

use App\Models\Conversion;
use App\Models\Video;
use App\Observers\ConversionObserver;
use App\Observers\VideoObserver;
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
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Video::observe(VideoObserver::class);
        Conversion::observe(ConversionObserver::class);
    }
}
