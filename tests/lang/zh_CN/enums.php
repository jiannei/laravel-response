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
use Jiannei\Response\Laravel\Tests\Enums\ResponseEnum;

return [
    200 => '成功',// 直接通过状态码取多语言

    // 结合 Enum 取多语言
    ResponseEnum::class => [
        // 标准 HTTP 状态码
        HttpStatusCode::HTTP_OK->value => '操作成功',
        HttpStatusCode::HTTP_UNAUTHORIZED->value => '授权失败',

        // 业务操作成功
        ResponseEnum::SERVICE_REGISTER_SUCCESS->value => '注册成功',
        ResponseEnum::SERVICE_LOGIN_SUCCESS->value => '登录成功',

        // 业务操作失败：授权业务
        ResponseEnum::SERVICE_REGISTER_ERROR->value => '注册失败',
        ResponseEnum::SERVICE_LOGIN_ERROR->value => '登录失败',

        // 客户端错误
        ResponseEnum::CLIENT_PARAMETER_ERROR->value => '参数错误',
        ResponseEnum::CLIENT_CREATED_ERROR->value => '数据已存在',
        ResponseEnum::CLIENT_DELETED_ERROR->value => '数据不存在',

        // 服务端错误
        ResponseEnum::SYSTEM_ERROR->value => '服务器错误',
        ResponseEnum::SYSTEM_UNAVAILABLE->value => '服务器正在维护，暂不可用',
        ResponseEnum::SYSTEM_CACHE_CONFIG_ERROR->value => '缓存配置错误',
        ResponseEnum::SYSTEM_CACHE_MISSED_ERROR->value => '缓存未命中',
        ResponseEnum::SYSTEM_CONFIG_ERROR->value => '系统配置错误',
    ],
];
