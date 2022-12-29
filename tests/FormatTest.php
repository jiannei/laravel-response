<?php

namespace Jiannei\Response\Laravel\Tests;

use Jiannei\Response\Laravel\Support\Facades\Response;

class FormatTest extends TestCase
{
    public function testAddExtraField()
    {
        $response = Response::success();

        $this->assertEquals(200, $response->status());

        $this->assertArrayHasKey('extra',$response->getData(true));
        $this->assertArrayHasKey('time',$response->getData(true)['extra']);
    }
}