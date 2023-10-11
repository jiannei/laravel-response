<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Jiannei\Response\Laravel\Tests\Repositories\Enums\ResponseCodeEnum;
use Jiannei\Response\Laravel\Tests\Repositories\Models\User;
use Jiannei\Response\Laravel\Tests\Repositories\Resources\UserCollection;
use Jiannei\Response\Laravel\Tests\Repositories\Resources\UserResource;

class SuccessTest extends TestCase
{
    use RefreshDatabase;

    public function testSuccess()
    {
        // 方式一：直接返回响应成功
        $response = Response::success(); // 注意：这里必须使用 this->response() 函数方式调用，因为 MakesHttpRequests 中有 response 属性

        $this->assertEquals(200, $response->status());

        $expectedJson = json_encode([
            'status' => 'success',
            'code' => 200,
            'message' => ResponseCodeEnum::fromValue(200)->description,
            'data' => (object) [],
            'error' => (object) [],
        ]);
        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }

    public function testCreated()
    {
        // 方式二：返回创建成功
        $response = Response::created();

        $this->assertEquals(201, $response->status());

        $expectedJson = json_encode([
            'status' => 'success',
            'code' => 201,
            'message' => ResponseCodeEnum::fromValue(201)->description,
            'data' => (object) [],
            'error' => (object) [],
        ]);
        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }

    public function testAccepted()
    {
        // 方式三：返回接收成功
        $response = Response::accepted();

        $this->assertEquals(202, $response->status());

        $expectedJson = json_encode([
            'status' => 'success',
            'code' => 202,
            'message' => ResponseCodeEnum::fromValue(202)->description,
            'data' => (object) [],
            'error' => (object) [],
        ]);
        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }

    public function testNoContent()
    {
        // 方式四：返回空内容；创建成功或删除成功等场景
        $response = Response::noContent();

        $this->assertEquals(204, $response->status());

        $expectedJson = json_encode([
            'status' => 'success',
            'code' => 204,
            'message' => ResponseCodeEnum::fromValue(204)->description,
            'data' => (object) [],
            'error' => (object) [],
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }

    public function testSuccessWithArrayData()
    {
        // 方式五：返回普通的数组
        $data = [
            'name' => 'Jiannei',
            'email' => 'longjian.huang@foxmail.com',
        ];
        $response = Response::success($data);

        $this->assertEquals(200, $response->status());

        $expectedJson = json_encode([
            'status' => 'success',
            'code' => 200,
            'message' => ResponseCodeEnum::fromValue(200)->description,
            'data' => $data,
            'error' => (object) [],
        ]);
        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }

    public function testSuccessWithResourceData()
    {
        // 方式六：返回 Api resource
        $user = User::factory()->make();
        $response = Response::success(new UserResource($user));

        $this->assertEquals(200, $response->status());
        $expectedJson = json_encode([
            'status' => 'success',
            'code' => 200,
            'message' => ResponseCodeEnum::fromValue(200)->description,
            'data' => [
                'nickname' => $user->name,
                'email' => $user->email,
            ],
            'error' => (object) [],
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }

    public function testSuccessWithCollectionData()
    {
        // 方式七：返回 Api collection
        $users = User::factory()->count(10)->make();
        $response = Response::success(new UserCollection($users));

        $this->assertEquals(200, $response->status());

        $data = $users->map(function ($item) {
            return [
                'nickname' => $item->name,
                'email' => $item->email,
            ];
        })->all();
        $expectedJson = json_encode([
            'status' => 'success',
            'code' => 200,
            'message' => ResponseCodeEnum::fromValue(200)->description,
            'data' => ['data' => $data],
            'error' => (object) [],
        ]);
        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }

    public function testSuccessWithPaginatedData()
    {
        // 方式八：返回分页的 Api collection
        User::factory()->count(20)->create();
        $users = User::query()->paginate();

        $response = Response::success(new UserCollection($users));

        $this->assertEquals(200, $response->status());

        $formatData = Arr::map($users->items(),fn($item) => [
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
            'message' => ResponseCodeEnum::fromValue(200)->description,
            'data' => $data,
            'error' => (object) [],
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }

    public function testSuccessWithMessage()
    {
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
    }

    public function testSuccessWithCustomMessageAndCode()
    {
        // 方式十：根据预定义的「业务码」和「对应的描述信息」返回
        $response = Response::success([], '', ResponseCodeEnum::SERVICE_LOGIN_SUCCESS);

        $expectedJson = json_encode([
            'status' => 'success',
            'code' => ResponseCodeEnum::SERVICE_LOGIN_SUCCESS, // 返回自定义的业务码
            'message' => ResponseCodeEnum::fromValue(ResponseCodeEnum::SERVICE_LOGIN_SUCCESS)->description, // 根据业务码取多语言的业务描述
            'data' => (object) [],
            'error' => (object) [],
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }

    public function testLengthAwarePaginator()
    {
        User::factory()->count(20)->create();
        $users = User::query()->paginate();

        $response = Response::success($users);

        $this->assertEquals(200, $response->status());

        $formatData = Arr::map($users->items(),fn($item) => $item->toArray());

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
            'message' => ResponseCodeEnum::fromValue(200)->description,
            'data' => $data,
            'error' => (object) [],
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }

    public function testSimplePaginator()
    {
        User::factory()->count(20)->create();
        $users = User::query()->simplePaginate();

        $response = Response::success($users);

        $this->assertEquals(200, $response->status());

        $formatData = Arr::map($users->items(),fn($item) => $item->toArray());

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
            'message' => ResponseCodeEnum::fromValue(200)->description,
            'data' => $data,
            'error' => (object) [],
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }

    public function testCursorPaginator()
    {
        User::factory()->count(20)->create();
        $users = User::query()->cursorPaginate();

        $response = Response::success($users);

        $this->assertEquals(200, $response->status());

        $formatData = Arr::map($users->items(),fn($item) => $item->toArray());

        $data = [
            'data' => $formatData,
            'meta' => [
                'cursor' => [
                    'current' => $users->cursor()?->encode(),
                    'prev' => $users->previousCursor()?->encode(),
                    'next' => $users->nextCursor()?->encode(),
                    'count' =>  count($users->items())
                ],
            ],
        ];
        $expectedJson = json_encode([
            'status' => 'success',
            'code' => 200,
            'message' => ResponseCodeEnum::fromValue(200)->description,
            'data' => $data,
            'error' => (object) [],
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }
}
