<?php

/*
 * This file is part of the Jiannei/laravel-response.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Providers;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Jiannei\Response\Laravel\Response;

class ServiceProvider extends IlluminateServiceProvider
{
    public function register()
    {
        $this->app->singleton(Response::class, function ($app) {
            return new Response();
        });

        $this->setupConfig();
    }

    protected function setupConfig()
    {
        $path = dirname(__DIR__, 2).'/config/response.php';

        $this->mergeConfigFrom($path, 'response');
    }
}
