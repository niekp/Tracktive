<?php

namespace App\Providers;

use App\Console\Commands\CreatePersonCommand;
use App\Console\Commands\NtfyCommand;
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
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
