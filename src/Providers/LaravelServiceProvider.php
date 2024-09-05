<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <jiannei@sinan.fun>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Providers;

use Illuminate\Support\ServiceProvider;
use Jiannei\Response\Laravel\Contract\ResponseFormat;
use Jiannei\Response\Laravel\Http\Exceptions\Handler;
use Jiannei\Response\Laravel\Support\Format;

class LaravelServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->setupConfig();


        $this->app->singleton(ResponseFormat::class, function ($app) {
            $formatter = $app->config->get('response.format.class');
            $config = $app->config->get('response.format.config');

            return match (true) {
                class_exists($formatter) && is_subclass_of($formatter, ResponseFormat::class) => new $formatter($config),
                default => new Format($config),
            };
        });
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
