<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <jiannei@sinan.fun>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Tests\Enums;

use Jiannei\Enum\Laravel\Support\Traits\EnumEnhance;

enum ResponseEnum: int
{
    use EnumEnhance;

    // 业务操作正确码：1xx、2xx、3xx 开头，后拼接 3 位
    // 200 + 001 => 200001，也就是有 001 ~ 999 个编号可以用来表示业务成功的情况，当然你可以根据实际需求继续增加位数，但必须要求是 200 开头
    // 举个栗子：你可以定义 001 ~ 099 表示系统状态；100 ~ 199 表示授权业务；200 ~ 299 表示用户业务...
    case SERVICE_REGISTER_SUCCESS = 200101;
    case SERVICE_LOGIN_SUCCESS = 200102;

    // 业务操作错误码（外部服务或内部服务调用...）
    case SERVICE_REGISTER_ERROR = 500101;
    case SERVICE_LOGIN_ERROR = 500102;

    // 客户端错误码：400 ~ 499 开头，后拼接 3 位
    case CLIENT_PARAMETER_ERROR = 400001;
    case CLIENT_CREATED_ERROR = 400002;
    case CLIENT_DELETED_ERROR = 400003;

    // 服务端操作错误码：500 ~ 599 开头，后拼接 3 位
    case SYSTEM_ERROR = 500001;
    case SYSTEM_UNAVAILABLE = 500002;
    case SYSTEM_CACHE_CONFIG_ERROR = 500003;
    case SYSTEM_CACHE_MISSED_ERROR = 500004;
    case SYSTEM_CONFIG_ERROR = 500005;
}
