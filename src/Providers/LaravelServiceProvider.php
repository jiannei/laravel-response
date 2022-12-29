<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Providers;

use Illuminate\Support\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->setupConfig();
    }

    public function boot()
    {
        $format = $this->app['config']['response']['format'];

        if (is_string($format) && class_exists($format)) {
            $this->app->bind(\Jiannei\Response\Laravel\Support\Format::class, $format);
        }
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
