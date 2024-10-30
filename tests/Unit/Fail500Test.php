<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <jiannei@sinan.fun>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Jiannei\Response\Laravel\Support\Facades\Response;

test('fail with 500 status code', function () {
    $response = Response::fail();

    expect($response->status())->toEqual(500)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'fail',
            'code' => 500,
            'message' => '',
            'data' => [],
            'error' => [],
        ]);
});
