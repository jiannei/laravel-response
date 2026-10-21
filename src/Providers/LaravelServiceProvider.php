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

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Jiannei\Response\Laravel\Http\Exceptions\Handler;
use Jiannei\Response\Laravel\Support\Config\ResponseConfig;
use Jiannei\Response\Laravel\Support\Format;

class LaravelServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->setupConfig();

        $this->app->singleton(
            ExceptionHandler::class,
            Handler::class
        );

        $this->app->singleton(ResponseConfig::class, function ($app) {
            $config = $app->config->get('response', []);

            return new ResponseConfig(is_array($config) ? $config : []);
        });

        $this->app->bind(Format::class, function ($app) {
            /** @var ResponseConfig $config */
            $config = $app->make(ResponseConfig::class);
            $formatter = $config->formatterClass();

            if (! class_exists($formatter) || ! is_a($formatter, Format::class, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid response formatter [%s]; it must extend [%s].',
                    $formatter,
                    Format::class
                ));
            }

            return new $formatter($config);
        });
    }

    protected function setupConfig(): void
    {
        $path = dirname(__DIR__, 2).'/config/response.php';

        if ($this->app->runningInConsole()) {
            $this->publishes([$path => config_path('response.php')], 'response');
        }

        $this->mergeConfigFrom($path, 'response');
    }
}
