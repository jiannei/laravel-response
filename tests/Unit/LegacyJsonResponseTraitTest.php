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
use Jiannei\Response\Laravel\Tests\Support\LegacyResponse;

test('legacy trait delegates success and fail to the response entry', function () {
    $legacy = new LegacyResponse;

    expect($legacy->success()->getData(true))->toEqual(Response::success()->getData(true))
        ->and($legacy->fail('error')->getData(true))->toEqual(Response::fail('error')->getData(true));
});

test('legacy trait keeps the shortcut methods and location headers', function () {
    $legacy = new LegacyResponse;

    expect($legacy->ok('成功')->status())->toEqual(200)
        ->and($legacy->errorNotFound()->status())->toEqual(404)
        ->and($legacy->accepted(location: '/next')->headers->get('Location'))->toEqual('/next')
        ->and($legacy->created(location: '/created')->headers->get('Location'))->toEqual('/created');
});

test('legacy trait accepts the old mixed error parameter', function () {
    $legacy = new LegacyResponse;

    $errors = ['email' => ['The email field is required.']];

    expect($legacy->fail('Validation failed', 422, $errors)->getData(true)['error'])->toEqual($errors)
        ->and($legacy->fail('error', 500, 'not-an-error-payload')->getData(true)['error'])->toEqual([]);
});
