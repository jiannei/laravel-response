<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Jiannei\Enum\Laravel\Support\Enums\HttpStatusCode;
use Jiannei\Response\Laravel\Support\Facades\Format;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Jiannei\Response\Laravel\Tests\Enums\ResponseEnum;
use Jiannei\Response\Laravel\Tests\Models\User;
use Jiannei\Response\Laravel\Tests\Resources\UserCollection;
use Jiannei\Response\Laravel\Tests\Resources\UserResource;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('success', function () {
    // 直接返回响应成功
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
    // 返回接收成功
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
    // 返回创建成功
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
    // 返回空内容；创建成功或删除成功等场景
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
    // 返回普通的数组
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

    // 返回分页数据
    $response = Response::success($users);

    expect($response->status())->toEqual(200)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'success',
            'code' => 200,
            'message' => '操作成功',
            'data' => Format::paginator($users),
            'error' => [],
        ]);
});

test('simple paginator', function () {
    User::factory()->count(20)->create();
    $users = User::query()->simplePaginate();

    // 返回简单分页数据
    $response = Response::success($users);

    expect($response->status())->toEqual(200)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'success',
            'code' => 200,
            'message' => '操作成功',
            'data' => Format::paginator($users),
            'error' => [],
        ]);
});

test('cursor paginator', function () {
    User::factory()->count(20)->create();
    $users = User::query()->cursorPaginate();

    // 返回游标分页数据
    $response = Response::success($users);

    expect($response->status())->toEqual(200)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'success',
            'code' => 200,
            'message' => '操作成功',
            'data' => Format::paginator($users),
            'error' => [],
        ]);
});

test('success with resource data', function () {
    // 返回 Api resource
    $user = User::factory()->make();

    $resource = new UserResource($user);
    $response = Response::success($resource);

    expect($response->status())->toEqual(200)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'success',
            'code' => 200,
            'message' => '操作成功',
            'data' => Format::jsonResource($resource),
            'error' => [],
        ]);
});

test('success with collection data', function () {
    // 返回 Api collection
    User::factory()->count(3)->create();
    $users = User::all();

    $collection = new UserCollection($users);
    $response = Response::success($collection);

    expect($response->status())->toEqual(200)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'success',
            'code' => 200,
            'message' => '操作成功',
            'data' => Format::resourceCollection($collection),
            'error' => [],
        ]);
});

test('success with paginated data', function () {
    // 返回分页的 Api collection
    User::factory()->count(20)->create();
    $users = User::query()->paginate();

    $collection = new UserCollection($users);

    $response = Response::success($collection);

    expect($response->status())->toEqual(200)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'success',
            'code' => 200,
            'message' => '操作成功',
            'data' => Format::resourceCollection($collection),
            'error' => [],
        ]);
});

test('success with message', function () {
    //返回指定的 Message
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
    // 返回指定的 message
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
    // 结合标准 HttpStatus Enum 返回多语言提示
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
    // 根据业务 ResponseEnum 指定 http 状态码，且有多语言提示
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
    // 根据预定义的「业务码」和「对应的描述信息」返回
    $response = Response::success(code: ResponseEnum::SERVICE_LOGIN_SUCCESS);

    expect($response->status())->toEqual(200)
        ->and($response->getData(true))->toMatchArray([
            'status' => 'success',
            'code' => 200102, // 返回自定义的业务码
            'message' => '登录成功', // 根据业务码取多语言的业务描述
            'data' => [],
            'error' => [],
        ]);
});
