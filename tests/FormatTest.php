<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Tests;

use Jiannei\Response\Laravel\Support\Facades\Response;

class FormatTest extends TestCase
{
    public function testAddExtraField()
    {
        $response = Response::success();

        $this->assertEquals(200, $response->status());

        $this->assertArrayHasKey('extra', $response->getData(true));
        $this->assertArrayHasKey('time', $response->getData(true)['extra']);
    }
}
