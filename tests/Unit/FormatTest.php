<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <jiannei@sinan.fun>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Illuminate\Pagination\LengthAwarePaginator;
use Jiannei\Response\Laravel\Support\Config\ResponseConfig;
use Jiannei\Response\Laravel\Support\Format;
use Jiannei\Response\Laravel\Tests\Enums\ResponseEnum;

function formatWith(array $config = []): Format
{
    return new Format(new ResponseConfig(array_replace_recursive([
        'locale' => 'enums.'.ResponseEnum::class,
        'error_code' => false,
        'format' => ['config' => []],
    ], $config)));
}

test('make builds the response envelope', function () {
    $response = formatWith()->make(['name' => 'Jiannei']);

    expect($response->status())->toEqual(200)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'success',
            'code' => 200,
            'message' => '操作成功',
            'data' => ['name' => 'Jiannei'],
            'error' => [],
        ]);
});

test('make carries the error payload', function () {
    $errors = ['email' => ['The email field is required.']];

    $response = formatWith()->make(message: 'Validation failed', code: 422, error: $errors);

    expect($response->status())->toEqual(422)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'error',
            'code' => 422,
            'message' => 'Validation failed',
            'error' => $errors,
        ]);
});

test('empty error is encoded as an object', function () {
    $response = formatWith()->make();

    expect($response->getData(true)['error'])->toEqual([])
        ->and($response->getContent())->toContain('"error":{}');
});

test('fields can be hidden and aliased', function () {
    $format = formatWith([
        'format' => [
            'config' => [
                'status' => ['alias' => 'status', 'show' => false],
                'message' => ['alias' => 'msg', 'show' => true],
                'error' => ['alias' => 'error', 'show' => false],
            ],
        ],
    ]);

    $data = $format->make()->getData(true);

    expect($data)->toMatchArray([
        'code' => 200,
        'msg' => '操作成功',
        'data' => [],
    ])->not->toHaveKey('status')
        ->not->toHaveKey('error');
});

test('nested data alias is applied before its parent alias', function () {
    $format = formatWith([
        'format' => [
            'config' => [
                'data' => ['alias' => 'payload', 'show' => true],
                'data.data' => ['alias' => 'data.rows', 'show' => true],
            ],
        ],
    ]);

    $paginator = new LengthAwarePaginator([['id' => 1], ['id' => 2]], 2, 15, 1, ['path' => '/']);
    $data = $format->make($paginator)->getData(true);

    expect($data)->toHaveKey('payload')
        ->not->toHaveKey('data')
        ->and($data['payload']['rows'])->toHaveCount(2)
        ->and($data['payload']['meta'])->toHaveKey('pagination');
});

test('nested fields can be hidden', function () {
    $format = formatWith([
        'format' => [
            'config' => [
                'data.data' => ['alias' => 'data.data', 'show' => false],
            ],
        ],
    ]);

    $paginator = new LengthAwarePaginator([['id' => 1], ['id' => 2]], 2, 15, 1, ['path' => '/']);
    $data = $format->make($paginator)->getData(true);

    expect($data['data'])->not->toHaveKey('data')
        ->and($data['data'])->toHaveKey('meta');
});

test('error_code only overrides failed responses', function () {
    $fail500 = formatWith(['error_code' => 500]);
    $fail200 = formatWith(['error_code' => 200]);

    expect($fail500->make(code: 200)->status())->toEqual(200)
        ->and($fail500->make(code: 500)->status())->toEqual(500)
        ->and($fail200->make(code: 500)->status())->toEqual(200)
        ->and($fail200->make(code: 400)->status())->toEqual(200);
});

test('business code drives status and localized message', function () {
    $response = formatWith()->make(code: ResponseEnum::CLIENT_PARAMETER_ERROR);

    expect($response->status())->toEqual(400)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'error',
            'code' => 400001,
            'message' => '参数错误',
        ]);
});
