<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Illuminate\Http\Exceptions\HttpResponseException;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Jiannei\Response\Laravel\Tests\Enums\ResponseEnum;


test('fail', function () {
    try {
        // 方式一：Controller 中直接返回失败，这里本质上是通过 JsonResponse 是抛出了一个 HttpResponseException，需要捕获异常后才能拿到真实响应
        // 不需要在前面加 return
        Response::fail();
    } catch (HttpResponseException $e) {
        $response = $e->getResponse();

        expect($response->getStatusCode())->toEqual(500)
            ->and($response->getContent())->toBeJson(json_encode([
                'status' => 'fail',
                'code' => 500,
                'message' => '',
                'data' => (object) [],
                'error' => (object) [],
            ]));
    }
});

test('fail with message', function () {
    try {
        // 方式二：Controller 中返回指定的 Message
        Response::fail('操作失败');
    } catch (HttpResponseException $e) {
        $response = $e->getResponse();

        expect($response->getStatusCode())->toEqual(500)
            ->and($response->getContent())->toBeJson(json_encode([
                'status' => 'fail',
                'code' => 500,
                'message' => '操作失败',
                'data' => (object) [],
                'error' => (object) [],
            ]));
    }
});

test('fail with custom code and message', function () {
    try {
        // 方式三：Controller 中返回预先定义的业务错误码和错误描述
        Response::fail(code: ResponseEnum::SERVICE_LOGIN_ERROR);
    } catch (HttpResponseException $e) {
        $response = $e->getResponse();

        expect($response->getStatusCode())->toEqual(500)
            ->and($response->getContent())->toBeJson(json_encode([
                'status' => 'fail',
                'code' => ResponseEnum::SERVICE_LOGIN_ERROR->value, // 预期返回指定的业务错误码
                'message' => ResponseEnum::fromValue(ResponseEnum::SERVICE_LOGIN_ERROR->value)->description(), // 预期根据业务码取相应的错误描述
                'data' => (object) [],
                'error' => (object) [],
            ]));
    }
});