<?php

use Illuminate\Support\Arr;
use Jiannei\Enum\Laravel\Support\Enums\HttpStatusCode;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Jiannei\Response\Laravel\Tests\Enums\ResponseEnum;
use Jiannei\Response\Laravel\Tests\Repositories\Models\User;
use Jiannei\Response\Laravel\Tests\Repositories\Resources\UserCollection;
use Jiannei\Response\Laravel\Tests\Repositories\Resources\UserResource;


uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('success', function () {
    // 方式一：直接返回响应成功
    $response = Response::success();

    expect($response->status())->toEqual(200)
        ->and($response->getContent())->toBeJson(json_encode([
            'status' => 'success',
            'code' => 200,
            'message' => '操作成功',
            'data' => [],
            'error' => (object) [],
        ]));
});

test('created', function () {
    // 方式二：返回创建成功
    $response = Response::created();

    expect($response->status())->toEqual(201)
        ->and($response->getContent())->toBeJson(json_encode([
            'status' => 'success',
            'code' => 201,
            'message' => '',
            'data' => (object) [],
            'error' => (object) [],
        ]));
});

test('accepted', function () {
    // 方式三：返回接收成功
    $response = Response::accepted();

    expect($response->status())->toEqual(202)
    ->and($response->getContent())->toBeJson(json_encode([
            'status' => 'success',
            'code' => 202,
            'message' => '',
            'data' => (object) [],
            'error' => (object) [],
        ]));
});

test('no content', function () {
    // 方式四：返回空内容；创建成功或删除成功等场景
    $response = Response::noContent();

    expect($response->status())->toEqual(204)
    ->and($response->getContent())->toBeJson(json_encode([
            'status' => 'success',
            'code' => 204,
            'message' => '',
            'data' => (object) [],
            'error' => (object) [],
        ]));
});

test('success with array data', function () {
    // 方式五：返回普通的数组
    $data = [
        'name' => 'Jiannei',
        'email' => 'longjian.huang@foxmail.com',
    ];
    $response = Response::success($data);

    expect($response->status())->toEqual(200);

    $expectedJson = json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => '操作成功',
        'data' => $data,
        'error' => (object) [],
    ]);
    $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
});

test('success with resource data', function () {
    // 方式六：返回 Api resource
    $user = User::factory()->make();
    $response = Response::success(new UserResource($user));

    expect($response->status())->toEqual(200);
    $expectedJson = json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => '操作成功',
        'data' => [
            'nickname' => $user->name,
            'email' => $user->email,
        ],
        'error' => (object) [],
    ]);

    $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
});

test('success with collection data', function () {
    // 方式七：返回 Api collection
    User::factory()->count(3)->create();
    $users = User::all();
    $response = Response::success(new UserCollection($users));

    expect($response->status())->toEqual(200);

    $data = $users->map(function ($item) {
        return [
            'nickname' => $item->name,
            'email' => $item->email,
        ];
    })->all();
    $expectedJson = json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => '操作成功',
        'data' => ['data' => $data],
        'error' => (object) [],
    ]);
    $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
});

test('success with paginated data', function () {
    // 方式八：返回分页的 Api collection
    User::factory()->count(20)->create();
    $users = User::query()->paginate();

    $response = Response::success(new UserCollection($users));

    expect($response->status())->toEqual(200);

    $formatData = Arr::map($users->items(), fn($item) => [
        'nickname' => $item->name,
        'email' => $item->email,
    ]);

    $data = [
        'data' => $formatData,
        'meta' => [
            'pagination' => [
                'count' => $users->lastItem(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'total' => $users->total(),
                'total_pages' => $users->lastPage(),
                'links' => array_filter([
                    'previous' => $users->previousPageUrl(),
                    'next' => $users->nextPageUrl(),
                ]),
            ],
        ],
    ];
    $expectedJson = json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => '操作成功',
        'data' => $data,
        'error' => (object) [],
    ]);

    $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
});

test('success with message', function () {
    // 方式九：返回指定的 Message
    $response = Response::success([], '成功');

    $expectedJson = json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => '成功',
        'data' => (object) [],
        'error' => (object) [],
    ]);

    $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
});

test('success with custom message and code', function () {
    // 方式十：根据预定义的「业务码」和「对应的描述信息」返回
    $response = Response::success([], '', ResponseEnum::SERVICE_LOGIN_SUCCESS);

    $expectedJson = json_encode([
        'status' => 'success',
        'code' => ResponseEnum::SERVICE_LOGIN_SUCCESS->value, // 返回自定义的业务码
        'message' => ResponseEnum::fromValue(ResponseEnum::SERVICE_LOGIN_SUCCESS->value)->description(), // 根据业务码取多语言的业务描述
        'data' => (object) [],
        'error' => (object) [],
    ]);

    $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
});

test('length aware paginator', function () {
    User::factory()->count(20)->create();
    $users = User::query()->paginate();

    $response = Response::success($users);

    expect($response->status())->toEqual(200);

    $formatData = Arr::map($users->items(), fn($item) => $item->toArray());

    $data = [
        'data' => $formatData,
        'meta' => [
            'pagination' => [
                'count' => $users->lastItem(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'total' => $users->total(),
                'total_pages' => $users->lastPage(),
                'links' => array_filter([
                    'previous' => $users->previousPageUrl(),
                    'next' => $users->nextPageUrl(),
                ]),
            ],
        ],
    ];
    $expectedJson = json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => '操作成功',
        'data' => $data,
        'error' => (object) [],
    ]);

    $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
});

test('simple paginator', function () {
    User::factory()->count(20)->create();
    $users = User::query()->simplePaginate();

    $response = Response::success($users);

    expect($response->status())->toEqual(200);

    $formatData = Arr::map($users->items(), fn($item) => $item->toArray());

    $data = [
        'data' => $formatData,
        'meta' => [
            'pagination' => [
                'count' => $users->lastItem(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'links' => array_filter([
                    'previous' => $users->previousPageUrl(),
                    'next' => $users->nextPageUrl(),
                ]),
            ],
        ],
    ];
    $expectedJson = json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => '操作成功',
        'data' => $data,
        'error' => (object) [],
    ]);

    $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
});

test('cursor paginator', function () {
    User::factory()->count(20)->create();
    $users = User::query()->cursorPaginate();

    $response = Response::success($users);

    expect($response->status())->toEqual(200);

    $formatData = Arr::map($users->items(), fn($item) => $item->toArray());

    $data = [
        'data' => $formatData,
        'meta' => [
            'cursor' => [
                'current' => $users->cursor()?->encode(),
                'prev' => $users->previousCursor()?->encode(),
                'next' => $users->nextCursor()?->encode(),
                'count' => count($users->items()),
            ],
        ],
    ];
    $expectedJson = json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => '操作成功',
        'data' => $data,
        'error' => (object) [],
    ]);

    $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
});
