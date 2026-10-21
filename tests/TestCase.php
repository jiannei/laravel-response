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
use Jiannei\Response\Laravel\Providers\LaravelServiceProvider;
use Jiannei\Response\Laravel\Support\Config\ResponseConfig;
use Jiannei\Response\Laravel\Tests\Enums\ResponseEnum;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->useResponseConfig();
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelServiceProvider::class,
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
    }

    /**
     * Bind a response config instance for the current test.
     *
     * @param  array<string, mixed>  $overrides
     */
    protected function useResponseConfig(array $overrides = []): void
    {
        $config = $this->app->config->get('response', []);
        $config = is_array($config) ? $config : [];
        $config = array_replace_recursive($config, $overrides);

        $this->app->instance(ResponseConfig::class, new ResponseConfig($config));
    }
}
