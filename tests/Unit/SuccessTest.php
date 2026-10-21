<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <jiannei@sinan.fun>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jiannei\Enum\Laravel\Support\Enums\HttpStatusCode;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Jiannei\Response\Laravel\Tests\Enums\ResponseEnum;
use Jiannei\Response\Laravel\Tests\Models\User;
use Jiannei\Response\Laravel\Tests\Resources\UserCollection;
use Jiannei\Response\Laravel\Tests\Resources\UserResource;

uses(RefreshDatabase::class);

test('success', function () {
    $response = Response::success();

    expect($response->status())->toEqual(200)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'success',
            'code' => 200,
            'message' => '操作成功',
            'data' => [],
            'error' => [],
        ]);
});

test('accepted', function () {
    $response = Response::accepted();

    expect($response->status())->toEqual(202)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'success',
            'code' => 202,
            'message' => '',
            'data' => [],
            'error' => [],
        ]);
});

test('created', function () {
    $response = Response::created();

    expect($response->status())->toEqual(201)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'success',
            'code' => 201,
            'message' => '',
            'data' => [],
            'error' => [],
        ]);
});

test('no content', function () {
    $response = Response::noContent();

    expect($response->status())->toEqual(204)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'success',
            'code' => 204,
            'message' => '',
            'data' => [],
            'error' => [],
        ]);
});

test('success with array data', function () {
    $data = [
        'name' => 'Jiannei',
        'email' => 'longjian.huang@foxmail.com',
    ];
    $response = Response::success($data);

    expect($response->status())->toEqual(200)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'success',
            'code' => 200,
            'message' => '操作成功',
            'data' => $data,
            'error' => [],
        ]);
});

test('length aware paginator', function () {
    User::factory()->count(20)->create();
    $users = User::query()->paginate();

    $response = Response::success($users);
    $pagination = $response->getData(true)['data']['meta']['pagination'];

    expect($response->status())->toEqual(200)
        ->and($response->getData(true)['data']['data'])->toHaveCount(15)
        ->and($pagination)->toHaveKeys([
            'count', 'per_page', 'current_page', 'total', 'total_pages', 'links',
        ])
        ->and($pagination['count'])->toEqual(15)
        ->and($pagination['per_page'])->toEqual(15)
        ->and($pagination['current_page'])->toEqual(1)
        ->and($pagination['total'])->toEqual(20)
        ->and($pagination['total_pages'])->toEqual(2);
});

test('simple paginator', function () {
    User::factory()->count(20)->create();
    $users = User::query()->simplePaginate();

    $response = Response::success($users);
    $pagination = $response->getData(true)['data']['meta']['pagination'];

    expect($response->status())->toEqual(200)
        ->and($response->getData(true)['data']['data'])->toHaveCount(15)
        ->and($pagination)->toHaveKeys(['count', 'per_page', 'current_page', 'links'])
        ->and($pagination['count'])->toEqual(15)
        ->and($pagination['per_page'])->toEqual(15)
        ->and($pagination['current_page'])->toEqual(1);
});

test('cursor paginator', function () {
    User::factory()->count(20)->create();
    $users = User::query()->cursorPaginate();

    $response = Response::success($users);

    expect($response->status())->toEqual(200)
        ->and($response->getData(true)['data']['data'])->toHaveCount(15)
        ->and($response->getData(true)['data']['meta']['cursor'])->toHaveKeys([
            'current', 'prev', 'next', 'count',
        ])
        ->and($response->getData(true)['data']['meta']['cursor']['count'])->toEqual(15);
});

test('success with resource data', function () {
    $user = User::factory()->make();

    $response = Response::success(new UserResource($user));

    expect($response->status())->toEqual(200)
        ->and($response->getData(true)['data'])->toMatchArray([
            'nickname' => $user->name,
            'email' => $user->email,
        ]);
});

test('success with collection data', function () {
    User::factory()->count(3)->create();
    $users = User::all();

    $response = Response::success(new UserCollection($users));

    expect($response->status())->toEqual(200)
        ->and($response->getData(true)['data']['data'])->toHaveCount(3)
        ->and($response->getData(true)['data']['data'][0])->toHaveKeys(['nickname', 'email']);
});

test('success with paginated data', function () {
    User::factory()->count(20)->create();
    $users = User::query()->paginate();

    $response = Response::success(new UserCollection($users));

    expect($response->status())->toEqual(200)
        ->and($response->getData(true)['data']['data'])->toHaveCount(15)
        ->and($response->getData(true)['data']['meta']['pagination']['total'])->toEqual(20);
});

test('success with message', function () {
    $response = Response::success(message: '成功');

    expect($response->status())->toEqual(200)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'success',
            'code' => 200,
            'message' => '成功',
            'data' => [],
            'error' => [],
        ]);
});

test('success alias ok', function () {
    $response = Response::ok('成功');

    expect($response->status())->toEqual(200)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'success',
            'code' => 200,
            'message' => '成功',
            'data' => [],
            'error' => [],
        ]);
});

test('localize', function () {
    $response = Response::localize(HttpStatusCode::HTTP_OK);

    expect($response->status())->toEqual(200)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'success',
            'code' => 200,
            'message' => '操作成功',
            'data' => [],
            'error' => [],
        ]);
});

test('localize and biz code', function () {
    $response = Response::localize(ResponseEnum::CLIENT_PARAMETER_ERROR);

    expect($response->status())->toEqual(400)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'error',
            'code' => 400001,
            'message' => '参数错误',
            'data' => [],
            'error' => [],
        ]);
});

test('success with custom message and code', function () {
    $response = Response::success(code: ResponseEnum::SERVICE_LOGIN_SUCCESS);

    expect($response->status())->toEqual(200)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'success',
            'code' => 200102,
            'message' => '登录成功',
            'data' => [],
            'error' => [],
        ]);
});

test('success and ok produce the same envelope', function () {
    expect(Response::success()->getData(true))
        ->toEqual(Response::ok()->getData(true));
});
