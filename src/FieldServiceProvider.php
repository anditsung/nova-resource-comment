<?php

namespace Anditsung\NovaResourceComment;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
        }

        $this->registerResources();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Nova::serving(function (ServingNova $event) {
            Nova::script('nova-resource-comment', __DIR__.'/../dist/js/field.js');
        });
    }

    protected function registerPublishing()
    {
        $this->publishes([
            __DIR__ . '/../config' => config_path('/'),
        ], 'nova-comment');
    }

    public function registerResources()
    {
        Nova::resources([
            config('nova-comment.nova'),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
