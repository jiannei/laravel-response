<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Jiannei\Response\Laravel\Support\Facades\Response;

test('add extra field', function () {
    $response = Response::success();

    expect($response->status())->toEqual(200)
        ->and($response->getData(true))->toHaveKey('extra')
        ->and($response->getData(true)['extra'])->toHaveKey('time');
});
