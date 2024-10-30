<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <jiannei@sinan.fun>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Tests;

use Illuminate\Contracts\Config\Repository;
use Jiannei\Response\Laravel\Tests\Enums\ResponseEnum;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            \Jiannei\Response\Laravel\Providers\LaravelServiceProvider::class,
        ];
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

    protected function defineEnvironment($app)
    {
        $app['path.lang'] = __DIR__.'/lang';

        tap($app['config'], function (Repository $config) {
            $config->set('app.locale', 'zh_CN');
            $config->set('database.default', 'sqlite');
            $config->set('database.connections.sqlite', [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]);

            $config->set('response.locale', 'enums.'.ResponseEnum::class);
        });

        if ($this instanceof \P\Tests\Unit\CustomFormatTest) {
            $app['config']->set('response.format', [
                'class' => Support\Format::class,
                'config' => [
                    // key => config
                    'status' => ['alias' => 'status', 'show' => false],
                    'code' => ['alias' => 'code', 'show' => true],
                    'message' => ['alias' => 'msg', 'show' => true],
                    'error' => ['alias' => 'error', 'show' => false],
                    'data' => ['alias' => 'data', 'show' => true],
                    'data.data' => ['alias' => 'data.data', 'show' => true], // rows/items/list
                ],
            ]);
        }

        if ($this instanceof \P\Tests\Unit\Fail200Test) {
            $app['config']->set('response.error_code', 200);
        }

        if ($this instanceof \P\Tests\Unit\Fail500Test) {
            $app['config']->set('response.error_code', 500);
        }
    }
}
