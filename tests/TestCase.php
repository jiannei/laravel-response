<?php

/*
 * This file is part of the Jiannei/laravel-response.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Tests;

use Jiannei\Response\Laravel\Providers\LumenServiceProvider;
use Jiannei\Response\Laravel\Tests\Repositories\Enums\ResponseCodeEnum;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/Database/migrations');
        $this->withFactories(__DIR__.'/Database/Factories');
    }

    protected function getPackageProviders($app)
    {
        return [
            LumenServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['path.lang'] = __DIR__.'/lang';

        $app['config']->set('database.default', 'sqlite');

        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('response.enum', ResponseCodeEnum::class);
    }
}
