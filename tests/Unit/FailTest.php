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
use Illuminate\Support\Arr;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Jiannei\Response\Laravel\Tests\Enums\ResponseEnum;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

uses(\Jiannei\Response\Laravel\Support\Traits\ExceptionTrait::class);

test('fail', function () {
    try {
        // 方式一：Controller 中直接返回失败，这里本质上是通过 JsonResponse 是抛出了一个 HttpResponseException，需要捕获异常后才能拿到真实响应
        // 不需要在前面加 return
        Response::fail();
    } catch (HttpResponseException $e) {
        $response = $e->getResponse();

        expect($response->getStatusCode())->toEqual(500);

        $expectedJson = json_encode([
            'status' => 'fail',
            'code' => 500,
            'message' => '',
            'data' => (object) [],
            'error' => (object) [],
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }
});

test('fail with message', function () {
    try {
        // 方式二：Controller 中返回指定的 Message
        Response::fail('操作失败');
    } catch (HttpResponseException $e) {
        $response = $e->getResponse();

        expect($response->getStatusCode())->toEqual(500);

        $expectedJson = json_encode([
            'status' => 'fail',
            'code' => 500,
            'message' => '操作失败',
            'data' => (object) [],
            'error' => (object) [],
        ]);
        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }
});

test('fail with custom code and message', function () {
    try {
        // 方式三：Controller 中返回预先定义的业务错误码和错误描述
        Response::fail(code: ResponseEnum::SERVICE_LOGIN_ERROR);
    } catch (HttpResponseException $e) {
        $response = $e->getResponse();

        expect($response->getStatusCode())->toEqual(500);

        $expectedJson = json_encode([
            'status' => 'fail',
            'code' => ResponseEnum::SERVICE_LOGIN_ERROR->value, // 预期返回指定的业务错误码
            'message' => ResponseEnum::fromValue(ResponseEnum::SERVICE_LOGIN_ERROR->value)->description(), // 预期根据业务码取相应的错误描述
            'data' => (object) [],
            'error' => (object) [],
        ]);
        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }
});

test('fail out controller', function () {
    try {
        // 方式四：Controller 中默认引入了 ResponseTrait；在没有引入 ResponseTrait 的地方可以直接使用 abort 来抛出 HttpException 异常然后返回错误信息
        abort(ResponseEnum::SYSTEM_ERROR->value);
    } catch (HttpException $httpException) {
        $response = Response::fail(
            '',
            isHttpException($httpException) ? $httpException->getStatusCode() : 500,
            convertExceptionToArray($httpException),
            isHttpException($httpException) ? $httpException->getHeaders() : [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );

        $expectedJson = json_encode([
            'status' => 'fail',
            'code' => ResponseEnum::SYSTEM_ERROR,
            'message' => ResponseEnum::fromValue(ResponseEnum::SYSTEM_ERROR->value)->description(),
            'data' => (object) [],
            'error' => convertExceptionToArray($httpException),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }
});

/**
 * Determine if the given exception is an HTTP exception.
 *
 * @param  Throwable  $e
 * @return bool
 */
function isHttpException(Throwable $e)
{
    return $e instanceof HttpExceptionInterface;
}

/**
 * Convert the given exception to an array.
 *
 * @param  Throwable  $e
 * @return array
 */
function convertExceptionToArray(Throwable $e)
{
    return config('app.debug', false) ? [
        'message' => $e->getMessage(),
        'exception' => get_class($e),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => collect($e->getTrace())->map(function ($trace) {
            return Arr::except($trace, ['args']);
        })->all(),
    ] : [
        'message' => isHttpException($e) ? $e->getMessage() : 'Server Error',
    ];
}
