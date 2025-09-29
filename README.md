<h1 align="center"> laravel-response </h1>

[简体中文](README.md) | [ENGLISH](README-EN.md)

> 为 Laravel 和 Lumen API 项目提供一个规范统一的响应数据格式。

> **🎉 最新更新：现已支持 Laravel 12！** 支持 Laravel 5.5 ~ 12.x 全版本，PHP 7.0 ~ 8.3。

![Test](https://github.com/Jiannei/laravel-response/workflows/Test/badge.svg)
[![Pest](https://img.shields.io/badge/Tests-Pest-green?style=flat)](https://pestphp.com)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%206-brightgreen?style=flat)](https://phpstan.org)
[![Code Coverage](https://img.shields.io/badge/Coverage-100%25-brightgreen?style=flat)](https://github.com/Jiannei/laravel-response)
[![Pint](https://img.shields.io/badge/Code%20Style-Pint-orange?style=flat)](https://laravel.com/docs/pint)
[![Latest Stable Version](https://poser.pugx.org/jiannei/laravel-response/v)](https://packagist.org/packages/jiannei/laravel-response)
[![Total Downloads](https://poser.pugx.org/jiannei/laravel-response/downloads)](https://packagist.org/packages/jiannei/laravel-response)
[![Monthly Downloads](https://poser.pugx.org/jiannei/laravel-response/d/monthly)](https://packagist.org/packages/jiannei/laravel-response)
[![License](https://poser.pugx.org/jiannei/laravel-response/license)](https://packagist.org/packages/jiannei/laravel-response)

## 社区讨论文章

- [是时候使用 Lumen 8 + API Resource 开发项目了！](https://learnku.com/articles/45311)
- [教你更优雅地写 API 之「路由设计」](https://learnku.com/articles/45526)
- [教你更优雅地写 API 之「规范响应数据」](https://learnku.com/articles/52784)
- [教你更优雅地写 API 之「枚举使用」](https://learnku.com/articles/53015)
- [教你更优雅地写 API 之「记录日志」](https://learnku.com/articles/53669)
- [教你更优雅地写 API 之「灵活地任务调度」](https://learnku.com/articles/58403)

## 介绍

`laravel-response` 主要用来统一 API 开发过程中「成功」、「失败」以及「异常」情况下的响应数据格式。

实现过程简单，在原有的 `\Illuminate\Http\JsonResponse`进行封装，使用时不需要有额外的心理负担。

遵循一定的规范，返回易于理解的 HTTP 状态码，并支持定义 `ResponseEnum` 来满足不同场景下返回描述性的业务操作码。

## 概览

- 统一的数据响应格式，固定包含：`code`、`status`、`data`、`message`、`error` (响应格式设计源于：[RESTful服务最佳实践](https://www.cnblogs.com/jaxu/p/7908111.html#a_8_2) )
- 你可以继续链式调用 `JsonResponse` 类中的所有 public 方法，比如 `Response::success()->header('X-foo','bar');`
- 合理地返回 Http 状态码，默认为 restful 严格模式，可以配置异常时返回 200 http 状态码（多数项目会这样使用）
- 支持格式化 Laravel 的 `Api Resource`、`Api  Resource Collection`、`Paginator`（简单分页）、`LengthAwarePaginator`（普通分页）、`Eloquent\Model`、`Eloquent\Collection`，以及简单的 `array` 和 `string`等格式数据返回
- 根据 debug 开关，合理返回异常信息、验证异常信息等
- 支持修改 Laravel 特地异常的状态码或提示信息，比如将 `No query results for model` 的异常提示修改成 `数据未找到`
- 支持配置返回字段是否显示，以及为她们设置别名，比如，将 `message` 别名设置为 `msg`，或者 分页数据第二层的 `data` 改成 `list`(res.data.data -> res.data.list)
- 分页数据格式化后的结果与使用 `league/fractal` （DingoApi 使用该扩展进行数据转换）的 transformer 转换后的格式保持一致，也就是说，可以顺滑地从 Laravel Api Resource 切换到 `league/fractal`
- 内置 Http 标准状态码支持，同时支持扩展 ResponseEnum 来根据不同业务模块定义响应码(可选，需要安装 `jiannei/laravel-enum`)
- 响应码 code 对应描述信息 message 支持本地化，支持配置多语言(可选，需要安装 `jiannei/laravel-enum`)


## 安装

支持 Laravel 5.5.* ~ Laravel 12.* 版本，自定义业务操作码部分依赖于  [jiannei/laravel-enum](https://github.com/Jiannei/laravel-enum)，需要先进行安装。

| laravel 版本 | lumen 版本   | response 版本 | enum 版本 | PHP 版本要求 |
|------------|------------|-------------|---------|------------|
| 5.5.*      | 5.5.*      | ~1.8        | ~1.4    | ^7.0       |
| 6.*        | 6.*        | ^2.0        | ~1.4    | ^7.2       |
| 7.*        | 7.*        | ^3.0        | ^2.0    | ^7.2.5     |
| 8.*        | 8.*        | ^4.0        | ^3.0    | ^7.3       |
| 9.* - 10.* | 9.* - 10.* | ^5.0        | ^3.0    | ^8.0       |
| 11.*       | 不支持      | ^6.0        | ^4.0    | ^8.2       |
| 12.*       | 不支持      | ^6.0        | ^4.0    | ^8.2       |

> **📝 版本说明：**
> - Laravel 11+ 版本内置了更好的异常处理机制，可以省略手动配置异常处理步骤
> - Lumen 从 Laravel 9 开始不再维护，建议使用 Laravel 进行 API 开发
> - 推荐使用最新版本以获得更好的性能和安全性

```shell
# laravel 5.5

composer require jiannei/laravel-response "~1.8" -vvv
composer require jiannei/laravel-enum "~1.4" -vvv # 可选

# laravel 6.x

composer require jiannei/laravel-response "^2.0" -vvv
composer require jiannei/laravel-enum "~1.4" -vvv # 可选

# laravel 7.x

composer require jiannei/laravel-response "^3.0" -vvv
composer require jiannei/laravel-enum "^2.0" -vvv # 可选

# laravel 8.x

composer require jiannei/laravel-response "^4.0" -vvv
composer require jiannei/laravel-enum "^3.0" -vvv # 可选

# laravel 9.x - 10.x

composer require jiannei/laravel-response "^5.0" -vvv
composer require jiannei/laravel-enum "^3.0" -vvv # 可选

# laravel 11.x

composer require jiannei/laravel-response "^6.0" -vvv
composer require jiannei/laravel-enum "^4.0" -vvv # 可选

# laravel 12.x

composer require jiannei/laravel-response "^6.0" -vvv
composer require jiannei/laravel-enum "^4.0" -vvv # 可选
```

## 配置

### Laravel

- 发布配置文件

```shell
$ php artisan vendor:publish --provider="Jiannei\Response\Laravel\Providers\LaravelServiceProvider"
```

- 格式化异常响应（Laravel 11+ 可省略这一步）


```php
// app/Exceptions/Handler.php
// 引入以后对于 API 请求产生的异常都会进行格式化数据返回
// 要求请求头 header 中包含 /json 或 +json，如：Accept:application/json
// 或者是 ajax 请求，header 中包含 X-Requested-With：XMLHttpRequest;

<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Jiannei\Response\Laravel\Support\Traits\ExceptionTrait;

class Handler extends ExceptionHandler
{
    use ExceptionTrait;
    // ...
}
```

### Lumen

- 复制配置文件到 `vendor/jiannei/laravel-response/config/response.php`，到 `config/response.php`

```bash
cp vendor/jiannei/laravel-response/config/response.php config/response.php
```

- 加载配置

```php
// bootstrap/app.php
$app->configure('response');
```

- 格式化异常响应

在 `app/Exceptions/Handler.php` 中 引入 `use Jiannei\Response\Laravel\Support\Traits\ExceptionTrait;`

在 `app/Http/Controllers/Controller.php` 中引入 `use Jiannei\Response\Laravel\Support\Traits\ExceptionTrait;`

- 注册服务容器

```php
$app->register(\Jiannei\Response\Laravel\Providers\LumenServiceProvider::class);
```

## 使用

扩展包本身提供了丰富的单元测试用例[tests](https://github.com/Jiannei/laravel-response/tree/main/tests) ，也可以通过查看测试用例来解锁使用方法。

或者查看相应的模板项目:

- [laravel-api-starter](https://github.com/Jiannei/laravel-api-starter)
- [lumen-api-starter](https://github.com/Jiannei/lumen-api-starter)

### 成功响应

#### 示例代码

```php
<?php
public function index()
{
    $users = User::all();

    return Response::success(new UserCollection($users));
}

public function paginate()
{
    $users = User::paginate(5);

    return Response::success(new UserCollection($users));
}

public function simplePaginate()
{
    $users = User::simplePaginate(5);

    return Response::success(new UserCollection($users));
}

public function item()
{
    $user = User::first();

    return Response::success(new UserResource($user));
}

public function array()
{
    return Response::success([
        'name' => 'Jiannel',
        'email' => 'longjian.huang@foxmail.com'
    ],'', ResponseEnum::SERVICE_REGISTER_SUCCESS);
}
```

#### 返回全部数据

支持自定义内层 data 字段名称，比如 rows、list

```json
{
    "status": "success",
    "code": 200,
    "message": "操作成功",
    "data": {
        "data": [
            {
                "nickname": "Joaquin Ondricka",
                "email": "lowe.chaim@example.org"
            },
            {
                "nickname": "Jermain D'Amore",
                "email": "reanna.marks@example.com"
            },
            {
                "nickname": "Erich Moore",
                "email": "ernestine.koch@example.org"
            }
        ]
    },
    "error": {}
}
```

#### 分页数据

支持自定义内层 data 字段名称，比如 rows、list

```json
{
    "status": "success",
    "code": 200,
    "message": "操作成功",
    "data": {
        "data": [
            {
                "nickname": "Joaquin Ondricka",
                "email": "lowe.chaim@example.org"
            },
            {
                "nickname": "Jermain D'Amore",
                "email": "reanna.marks@example.com"
            },
            {
                "nickname": "Erich Moore",
                "email": "ernestine.koch@example.org"
            },
            {
                "nickname": "Eva Quitzon",
                "email": "rgottlieb@example.net"
            },
            {
                "nickname": "Miss Gail Mitchell",
                "email": "kassandra.lueilwitz@example.net"
            }
        ],
        "meta": {
            "pagination": {
                "count": 5,
                "per_page": 5,
                "current_page": 1,
                "total": 12,
                "total_pages": 3,
                "links": {
                    "previous": null,
                    "next": "http://laravel-api.test/api/users/paginate?page=2"
                }
            }
        }
    },
    "error": {}
}
```

#### 返回简单分页数据

支持自定义内层 data 字段名称，比如 rows、list

```json
{
    "status": "success",
    "code": 200,
    "message": "操作成功",
    "data": {
        "data": [
            {
                "nickname": "Joaquin Ondricka",
                "email": "lowe.chaim@example.org"
            },
            {
                "nickname": "Jermain D'Amore",
                "email": "reanna.marks@example.com"
            },
            {
                "nickname": "Erich Moore",
                "email": "ernestine.koch@example.org"
            },
            {
                "nickname": "Eva Quitzon",
                "email": "rgottlieb@example.net"
            },
            {
                "nickname": "Miss Gail Mitchell",
                "email": "kassandra.lueilwitz@example.net"
            }
        ],
        "meta": {
            "pagination": {
                "count": 5,
                "per_page": 5,
                "current_page": 1,
                "links": {
                    "previous": null,
                    "next": "http://laravel-api.test/api/users/simple-paginate?page=2"
                }
            }
        }
    },
    "error": {}
}
```

#### 返回单条数据

```json
{
    "status": "success",
    "code": 200,
    "message": "操作成功",
    "data": {
        "nickname": "Joaquin Ondricka",
        "email": "lowe.chaim@example.org"
    },
    "error": {}
}
```

#### 其他快捷方法

```php
Response::ok();// 无需返回 data，只返回 message 情形的快捷方法
Response::localize(200101);// 无需返回 data，message 根据响应码配置返回的快捷方法
Response::accepted();
Response::created();
Response::noContent();
```

### 失败响应

#### 不指定 message

```php
public function fail()
{
    return Response::fail();
}
```

- 未配置多语言响应描述

```json
{
    "status": "fail",
    "code": 500,
    "message": "Http internal server error",
    "data": {},
    "error": {}
}
```

- 配置多语言描述

```json
{
    "status": "fail",
    "code": 500,
    "message": "操作失败",
    "data": {},
    "error": {}
}
```

#### 指定 message

```php
public function fail()
{
    return Response::fail('error');
}
```

返回数据

```json
{
    "status": "fail",
    "code": 500,
    "message": "error",
    "data": {},
    "error": {}
}
```

#### 指定 code

```php
public function fail()
{
    return Response::fail('',ResponseEnum::SERVICE_LOGIN_ERROR);
}
```

返回数据

```json
{
    "status": "fail",
    "code": 500102,
    "message": "登录失败",
    "data": {},
    "error": {}
}
```

#### 其他快捷方法

```php
Response::errorBadRequest();
Response::errorUnauthorized();
Response::errorForbidden();
Response::errorNotFound();
Response::errorMethodNotAllowed();
Response::errorInternal();
```

### 异常响应

#### 表单验证异常

```json
{
    "status": "error",
    "code": 422,
    "message": "验证失败",
    "data": {},
    "error": {
        "email": [
            "The email field is required."
        ]
    }
}
```

#### Controller 以外抛出异常

可以使用 abort 辅助函数抛出 HttpException 异常

```php
abort(500102,'登录失败');

// 返回数据

{
    "status": "fail",
    "code": 500102,
    "message": "登录失败",
    "data": {},
    "error": {}
}
```

#### 其他异常

开启 debug（`APP_DEBUG=true`）

```json
{
    "status": "error",
    "code": 404,
    "message": "Http not found",
    "data": {},
    "error": {
        "message": "",
        "exception": "Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException",
        "file": "/home/vagrant/code/laravel-api-starter/vendor/laravel/framework/src/Illuminate/Routing/AbstractRouteCollection.php",
        "line": 43,
        "trace": [
            {
                "file": "/home/vagrant/code/laravel-api-starter/vendor/laravel/framework/src/Illuminate/Routing/RouteCollection.php",
                "line": 162,
                "function": "handleMatchedRoute",
                "class": "Illuminate\\Routing\\AbstractRouteCollection",
                "type": "->"
            },
            {
                "file": "/home/vagrant/code/laravel-api-starter/vendor/laravel/framework/src/Illuminate/Routing/Router.php",
                "line": 646,
                "function": "match",
                "class": "Illuminate\\Routing\\RouteCollection",
                "type": "->"
            }
        ]
    }
}
```

关闭 debug

```json
{
    "status": "error",
    "code": 404,
    "message": "Http not found",
    "data": {},
    "error": {}
}
```

### 自定义业务码

```php
<?php
namespace App\Enums;

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
```

### 本地化业务码描述

```php
<?php
// lang/zh_CN/enums.php
use App\Repositories\Enums\ResponseEnum;
use Jiannei\Enum\Laravel\Support\Enums\HttpStatusCode;

return [
    // 响应状态码
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
    ],
];
```

## 由 JetBrains 赞助

非常感谢 Jetbrains 为我提供的 IDE 开源许可，让我完成此项目和其他开源项目上的开发工作。

[![](https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.svg)](https://www.jetbrains.com/?from=https://github.com/jiannei)

## Stargazers over time

[![Stargazers over time](https://starchart.cc/jiannei/laravel-response.svg)](https://starchart.cc/jiannei/laravel-response)

## 协议

MIT 许可证（MIT）。有关更多信息，请参见[协议文件](LICENSE)。