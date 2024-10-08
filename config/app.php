<?php

use Illuminate\Support\ServiceProvider;

return [
    'providers' => ServiceProvider::defaultProviders()->merge([
        App\Providers\AppServiceProvider::class,
    ])->toArray(),
];
