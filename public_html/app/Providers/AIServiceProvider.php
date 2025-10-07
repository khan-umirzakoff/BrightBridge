<?php

namespace App\Providers;

use App\AiSetting;
use App\Contracts\AIService;
use App\Services\GeminiAIService;
use App\Services\OpenAIService;
use Illuminate\Support\ServiceProvider;

class AIServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(AIService::class, function ($app) {
            // Try to get provider from database, fallback to .env
            try {
                $provider = AiSetting::getProvider();
            } catch (\Exception $e) {
                // Fallback to .env if database not available (e.g., during migration)
                $provider = strtolower(env('AI_PROVIDER', 'gemini'));
            }

            switch ($provider) {
                case 'openai':
                case 'chatgpt':
                case 'gpt':
                    return new OpenAIService();

                case 'gemini':
                case 'google':
                default:
                    return new GeminiAIService();
            }
        });
    }

    public function boot()
    {
        //
    }
}

