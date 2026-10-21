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
use Jiannei\Response\Laravel\Support\Format;
use Jiannei\Response\Laravel\Tests\Support\Format as CustomFormat;

uses()->beforeEach(function () {
    $this->useResponseConfig([
        'format' => [
            'class' => CustomFormat::class,
            'config' => [
                'status' => ['alias' => 'status', 'show' => false],
                'code' => ['alias' => 'code', 'show' => true],
                'message' => ['alias' => 'msg', 'show' => true],
                'error' => ['alias' => 'error', 'show' => false],
                'data' => ['alias' => 'data', 'show' => true],
            ],
        ],
    ]);
})->in(__FILE__);

test('custom formatter can add fields', function () {
    $response = Response::success();

    expect($response->status())->toEqual(200)
        ->and($response->getData(true))->toHaveKey('extra')
        ->and($response->getData(true)['extra'])->toHaveKey('time');
});

test('custom formatter can hide and alias fields', function () {
    $data = Response::success()->getData(true);

    expect($data)->toMatchArray([
        'code' => 200,
        'data' => [],
        'msg' => '操作成功',
    ])->not->toHaveKey('status')
        ->not->toHaveKey('error');
});

test('invalid formatter class is rejected', function () {
    $this->useResponseConfig([
        'format' => ['class' => stdClass::class],
    ]);

    expect(fn () => app(Format::class))
        ->toThrow(InvalidArgumentException::class, 'Invalid response formatter');
});
