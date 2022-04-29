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

class LumenServiceProvider extends LaravelServiceProvider
{
    public function boot()
    {
        $this->app->configure('response');
    }

    protected function setupConfig()
    {
        $path = dirname(__DIR__, 2).'/config/response.php';

        $this->mergeConfigFrom($path, 'response');
    }
}
