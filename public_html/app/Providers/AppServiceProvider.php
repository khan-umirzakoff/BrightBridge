<?php

namespace App\Providers;

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
        \App\Jobs::observe(\App\Observers\JobObserver::class);
        \App\News::observe(\App\Observers\NewsObserver::class);
        \App\Trainings::observe(\App\Observers\TrainingsObserver::class);
        \App\AiKnowledge::observe(\App\Observers\AiKnowledgeObserver::class);
    }
}
