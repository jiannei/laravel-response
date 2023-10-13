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
use Jiannei\Response\Laravel\Tests\Enums\ResponseEnum;

test('fail', function () {
    // Controller 中直接返回失败
    $response = Response::fail();

    expect($response->status())->toEqual(500)
        ->and($response->getData(true))->toEqual([
            'status' => 'fail',
            'code' => 500,
            'message' => '',
            'data' => [],
            'error' => [],
        ]);
});

test('fail with message', function () {
    // Controller 中返回指定的 Message
    $response = Response::fail('操作失败');

    expect($response->status())->toEqual(500)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'fail',
            'code' => 500,
            'message' => '操作失败',
            'data' => [],
            'error' => [],
        ]);
});

test('fail with custom code and message', function () {
    // Controller 中返回预先定义的业务错误码和错误描述
    $response = Response::fail(code: ResponseEnum::SERVICE_LOGIN_ERROR);

    expect($response->status())->toEqual(500)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'fail',
            'code' => 500102, // 预期返回指定的业务错误码
            'message' => '登录失败', // 预期根据业务码取相应的错误描述
            'data' => [],
            'error' => [],
        ]);
});

test('error bad request', function () {
    $response = Response::errorBadRequest('非法请求');

    expect($response->status())->toEqual(400)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'error',
            'code' => 400,
            'message' => '非法请求',
            'data' => [],
            'error' => [],
        ]);
});

test('error unauthorized', function () {
    $response = Response::errorUnauthorized();

    expect($response->status())->toEqual(401)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'error',
            'code' => 401,
            'message' => '授权失败',
            'data' => [],
            'error' => [],
        ]);
});

test('error forbidden', function () {
    $response = Response::errorForbidden();

    expect($response->status())->toEqual(403)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'error',
            'code' => 403,
            'message' => '',
            'data' => [],
            'error' => [],
        ]);
});

test('error not found', function () {
    $response = Response::errorNotFound();

    expect($response->status())->toEqual(404)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'error',
            'code' => 404,
            'message' => '',
            'data' => [],
            'error' => [],
        ]);
});

test('error method not allowed', function () {
    $response = Response::errorMethodNotAllowed();

    expect($response->status())->toEqual(405)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'error',
            'code' => 405,
            'message' => '',
            'data' => [],
            'error' => [],
        ]);
});
