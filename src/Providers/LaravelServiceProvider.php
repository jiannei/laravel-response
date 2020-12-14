<?php


namespace Jiannei\Response\Laravel\Providers;


use Illuminate\Support\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->setupConfig();
    }

    protected function setupConfig()
    {
        $path = dirname(__DIR__, 2).'/config/response.php';

        if ($this->app->runningInConsole()) {
            $this->publishes([$path => config_path('response.php')], 'response');
        }

        $this->mergeConfigFrom($path, 'response');
    }
}