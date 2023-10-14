<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Jiannei\Response\Laravel\Support\Facades\Format;
use Jiannei\Response\Laravel\Support\Facades\Response;

test('add extra field', function () {
    $response = Response::success();

    expect($response->status())->toEqual(200)
        ->and($response->getData(true))->toHaveKey('extra')
        ->and($response->getData(true)['extra'])->toHaveKey('time');
});

/**
 * 'class' => \Jiannei\Response\Laravel\Tests\Support\Format::class,
 * 'config' => [
 *      // key => config
 *      'status' => ['alias' => 'status', 'show' => false],
 *      'code' => ['alias' => 'code', 'show' => true],
 *      'message' => ['alias' => 'msg', 'show' => true],
 *      'error' => ['alias' => 'error', 'show' => false],
 *      'data' => ['alias' => 'data', 'show' => true],
 *      'data.data' => ['alias' => 'data.data', 'show' => true], // rows/items/list
 * ]
 */
test('hide some field', function () {
    // 隐藏 status，message 字段名称修改成 msg
    $data = Format::data()->get();

    expect($data)->toMatchArray([
        'code' => 200,
        'data' => (object)[],
        'msg' => '操作成功'
    ]);
});
