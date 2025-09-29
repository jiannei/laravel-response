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

class LumenServiceProvider extends LaravelServiceProvider
{
    protected function setupConfig(): void
    {
        $path = dirname(__DIR__, 2).'/config/response.php';

        $this->mergeConfigFrom($path, 'response');
    }
}
