<?php

namespace App\Providers;

use App\Modules\Notes\Services\AiService;
use App\Modules\Notes\Services\FakeProvider;
use App\Modules\Notes\Services\GeminiProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AiService::class, fn () => match (config('services.notes.provider')) {
            'fake' => new FakeProvider,
            'gemini' => new GeminiProvider,
            default => throw new \InvalidArgumentException('Provider notulen tidak dikenal.'),
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
