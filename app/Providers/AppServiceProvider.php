<?php

namespace App\Providers;

use App\Console\Commands\CreatePersonCommand;
use App\Console\Commands\NtfyCommand;
use App\Console\Commands\ReprocessDataCommand;
use App\Cronjobs\GarbageCollectionCommand;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->commands([
            CreatePersonCommand::class,
            NtfyCommand::class,
            ReprocessDataCommand::class,
            GarbageCollectionCommand::class,
            ImportPhoneTrackCommand::class,
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
