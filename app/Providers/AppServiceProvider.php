<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Log;
use App\Models\Conversion;
use App\Models\Video;
use App\Observers\ConversionObserver;
use App\Observers\VideoObserver;

class AppServiceProvider extends ServiceProvider
{
    public const HOME = '/';

    public function register()
    {
        //
    }

    public function boot()
    {
        Video::observe(VideoObserver::class);
        Conversion::observe(ConversionObserver::class);

        $this->bootEvent();
        $this->bootRoute();
    }

    public function bootEvent(): void
    {
        Event::listen(function (JobFailed $event) {
            Log::error('Job failed', [
                'connection' => $event->connectionName,
                'job' => $event->job,
                'exception' => $event->exception,
            ]);
        });
    }

    public function bootRoute(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
