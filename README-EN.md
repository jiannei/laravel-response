<h1 align="center"> laravel-response </h1>

[简体中文](README.md) | [ENGLISH](README-EN.md)

> Provide a standardized and unified response data format for Laravel API projects.

> **🎉 Latest Update: Now supports Laravel 13!** Supports Laravel 5.5 ~ 13.x all versions, PHP 7.0 ~ 8.5.

![Test](https://github.com/Jiannei/laravel-response/workflows/Test/badge.svg)
[![Pest](https://img.shields.io/badge/Tests-Pest-green?style=flat)](https://pestphp.com)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%206-brightgreen?style=flat)](https://phpstan.org)
[![Code Coverage](https://img.shields.io/badge/Coverage-100%25-brightgreen?style=flat)](https://github.com/Jiannei/laravel-response)
[![Pint](https://img.shields.io/badge/Code%20Style-Pint-orange?style=flat)](https://laravel.com/docs/pint)
[![Latest Stable Version](https://poser.pugx.org/jiannei/laravel-response/v)](https://packagist.org/packages/jiannei/laravel-response)
[![Total Downloads](https://poser.pugx.org/jiannei/laravel-response/downloads)](https://packagist.org/packages/jiannei/laravel-response)
[![Monthly Downloads](https://poser.pugx.org/jiannei/laravel-response/d/monthly)](https://packagist.org/packages/jiannei/laravel-response)
[![License](https://poser.pugx.org/jiannei/laravel-response/license)](https://packagist.org/packages/jiannei/laravel-response)

## Introduce

`laravel-response` It is mainly used to unify the response data format of "success", "failure" and "exception" in the process of API development.

It is encapsulated in the original `\Illuminate\Http\JsonResponse`, there is nothing complicated.

Follow certain specifications, return HTTP status codes that are easy to understand, and support the definition of 'enum' to meet the return of descriptive business operation codes in different scenarios.

## Features

- Unified data response format, including:`code`、`status`、`data`、`message`、`error`
- You can continue to chain call all public methods in the `JsonResponse` class, such as `Response::success()->header('x-foo ',' bar ')`
- Reasonably return the HTTP status code. The default is restful strict mode. You can configure to return 200 HTTP status code in case of exception (this is how most projects use it)
- It supports the formatting of `Api Resource`、`Api  Resource Collection`、`Paginator`、`LengthAwarePaginator`、`Eloquent\Model`、`Eloquent\Collection`,as well as the return of data in simple formats such as `array` 和 `string`
- According to the debug config, reasonably return the exception information, verify the exception information
- It supports modifying the status code or prompt information of laravel's special exception, such as modifying the exception prompt of `No query results for model` to `data not found`
- Any returned field can be displayed or hidden, and aliases can be set, such as alia `message` to `msg`
- The formatted result of paging data is consistent with the format converted by the transformer using `league/fractal` (DingoApi uses this extension for data conversion), that is, it can be smoothly switched from laravel API resource to `league/fractal`
- Built in HTTP standard status code support, and support the extension of ResponseCodeEnum to define response codes according to different business modules (optional, need to install `jiannei/laravel-enum`)

## Install

Support for Laravel 5.5.* ~ Laravel 13.*, the user-defined business operation code partially depends on [jiannei/laravel-enum](https://github.com/Jiannei/laravel-enum).

| laravel version | response version | enum version | PHP version |
|------------| ----  |-------------|  ----  |------------|
| 5.5.*      | ~1.8        | ~1.4    | ^7.0       |
| 6.*        | ^2.0        |  ~1.4  | ^7.2       |
| 7.*        | ^3.0        |  ^2.0  | ^7.2.5     |
| 8.*  | ^4.0        |  ^3.0  | ^7.3       |
| 9.* - 10.* | ^5.0        |  ^3.0  | ^8.0       |
| 11.*       | ^6.0        |  ^4.0  | ^8.2       |
| 12.*       | ^6.0        |  ^4.0  | ^8.2       |
| 13.*       | ^7.0        |  ^4.0  | ^8.3       |


```shell
# laravel 5.5

composer require jiannei/laravel-response "~1.8" -vvv
composer require jiannei/laravel-enum "~1.4" -vvv # optional

# laravel 6.x

composer require jiannei/laravel-response "^2.0" -vvv
composer require jiannei/laravel-enum "~1.4" -vvv # optional

# laravel 7.x

composer require jiannei/laravel-response "^3.0" -vvv
composer require jiannei/laravel-enum "^2.0" -vvv # optional

# laravel 8.x

composer require jiannei/laravel-response "^4.0" -vvv
composer require jiannei/laravel-enum "^3.0" -vvv # optional

# laravel 9.x - 10.x

composer require jiannei/laravel-response "^5.0" -vvv
composer require jiannei/laravel-enum "^3.0" -vvv # optional

# laravel 11.x

composer require jiannei/laravel-response "^6.0" -vvv
composer require jiannei/laravel-enum "^4.0" -vvv # optional

# laravel 12.x

composer require jiannei/laravel-response "^6.0" -vvv
composer require jiannei/laravel-enum "^4.0" -vvv # optional

# laravel 13.x

composer require jiannei/laravel-response "^7.0" -vvv
composer require jiannei/laravel-enum "^4.0" -vvv # optional
```

> **📝 Version Notes:**
> - Laravel 11+ has built-in better exception handling mechanism, you can skip manual exception handling configuration
> - Lumen is no longer maintained; this package no longer provides a Lumen adapter
> - It's recommended to use the latest version for better performance and security

## Configuration

### Laravel

- publish config file

```shell
$ php artisan vendor:publish --provider="Jiannei\Response\Laravel\Providers\LaravelServiceProvider"
```

- format exception response (Laravel 11+ can skip this step)

```php
// app/Exceptions/Handler.php
// The exceptions generated by API requests will be formatted and returned
// The request header is required to contain /json or +json, such as Accept:application/json
// Or Ajax request, the header contains X-Requested-With：XMLHttpRequest;

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

## Usage

The extension package itself provides rich unit test cases,you can unlock usage by viewing test cases [tests](https://github.com/Jiannei/laravel-response/tree/main/tests)

Or view the corresponding template projects:

- [laravel-api-starter](https://github.com/Jiannei/laravel-api-starter)

### Successful response

#### Sample code

```php
<?php
// ...
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
    ],'', ResponseCodeEnum::SERVICE_REGISTER_SUCCESS);
}


```

#### Return all rows data

Support custom inner `data` field names, such as `rows` or `list`

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

#### Paginator

Support custom inner `data` field names, such as `rows` or `list`

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

#### Simple Paginator

Support custom inner `data` field names, such as `rows` or `list`

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

#### Return one row data

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

#### Other shortcuts

```php
Response::ok();// There is no need to return data, but only message
Response::localize(200101);// There is no need to return data, return message according to the response code
Response::accepted();
Response::created();
Response::noContent();
```

### Failure response

#### Do not specify message

```php
public function fail()
{
    Response::fail();// 不需要加 return
}
```

- localization is not configured

```json
{
    "status": "fail",
    "code": 500,
    "message": "Http internal server error",
    "data": {},
    "error": {}
}
```

- localization is configured

```json
{
    "status": "fail",
    "code": 500,
    "message": "操作失败",
    "data": {},
    "error": {}
}
```

#### Specify message

```php
public function fail()
{
    Response::fail('error');// 不需要加 return
}
```

return response

```json
{
    "status": "fail",
    "code": 500,
    "message": "error",
    "data": {},
    "error": {}
}
```

#### Specify code

```php
public function fail()
{
    Response::fail('',ResponseCodeEnum::SERVICE_LOGIN_ERROR);
}
```

return response

```json
{
    "status": "fail",
    "code": 500102,
    "message": "登录失败",
    "data": {},
    "error": {}
}
```

#### Other shortcuts

```php
Response::errorBadRequest();
Response::errorUnauthorized();
Response::errorForbidden();
Response::errorNotFound();
Response::errorMethodNotAllowed();
Response::errorInternal();
```

### Exception response

#### Form validation exception

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

#### An exception is thrown outside the controller

You can throw an `HttpException`  using the `abort` helper function

```php
abort(500102,'登录失败');

// return response
{
    "status": "fail",
    "code": 500102,
    "message": "登录失败",
    "data": {},
    "error": {}
}
```

#### Other exceptions

enable debug mode(`APP_DEBUG=true`)

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

disable debug mode(`APP_DEBUG=false`)

```json
{
    "status": "error",
    "code": 404,
    "message": "Http not found",
    "data": {},
    "error": {}
}
```

### Custom business code

```php
<?php
namespace App\Enums;

use Jiannei\Enum\Laravel\Repositories\Enums\HttpStatusCodeEnum;

class ResponseCodeEnum extends HttpStatusCodeEnum
{
    // success code,Start with 1xx、2xx、3xx
    const SERVICE_REGISTER_SUCCESS = 200101;
    const SERVICE_LOGIN_SUCCESS = 200102;

    // client error code,Start with 400 ~ 499
    const CLIENT_PARAMETER_ERROR = 400001;
    const CLIENT_CREATED_ERROR = 400002;
    const CLIENT_DELETED_ERROR = 400003;
    const CLIENT_VALIDATION_ERROR = 422001; // form validation error

    // service error code,Start with 500 ~ 599
    const SYSTEM_ERROR = 500001;
    const SYSTEM_UNAVAILABLE = 500002;
    const SYSTEM_CACHE_CONFIG_ERROR = 500003;
    const SYSTEM_CACHE_MISSED_ERROR = 500004;
    const SYSTEM_CONFIG_ERROR = 500005;
}
```

### Localized code description

```php
<?php
// lang/zh_CN/enums.php
use App\Repositories\Enums\ResponseCodeEnum;

return [
    // 响应状态码
    ResponseCodeEnum::class => [
        // 成功
        ResponseCodeEnum::HTTP_OK => '操作成功', // 自定义 HTTP 状态码返回消息
        ResponseCodeEnum::HTTP_INTERNAL_SERVER_ERROR => '操作失败', // 自定义 HTTP 状态码返回消息
        ResponseCodeEnum::HTTP_UNAUTHORIZED => '授权失败',

        // 业务操作成功
        ResponseCodeEnum::SERVICE_REGISTER_SUCCESS => '注册成功',
        ResponseCodeEnum::SERVICE_LOGIN_SUCCESS => '登录成功',

        // 客户端错误
        ResponseCodeEnum::CLIENT_PARAMETER_ERROR => '参数错误',
        ResponseCodeEnum::CLIENT_CREATED_ERROR => '数据已存在',
        ResponseCodeEnum::CLIENT_DELETED_ERROR => '数据不存在',
        ResponseCodeEnum::CLIENT_VALIDATION_ERROR => '表单验证错误',

        // 服务端错误
        ResponseCodeEnum::SYSTEM_ERROR => '服务器错误',
        ResponseCodeEnum::SYSTEM_UNAVAILABLE => '服务器正在维护，暂不可用',
        ResponseCodeEnum::SYSTEM_CACHE_CONFIG_ERROR => '缓存配置错误',
        ResponseCodeEnum::SYSTEM_CACHE_MISSED_ERROR => '缓存未命中',
        ResponseCodeEnum::SYSTEM_CONFIG_ERROR => '系统配置错误',

        // 业务操作失败：授权业务
        ResponseCodeEnum::SERVICE_REGISTER_ERROR => '注册失败',
        ResponseCodeEnum::SERVICE_LOGIN_ERROR => '登录失败',
    ],
];
```

## Project supported by JetBrains

Many thanks to Jetbrains for kindly providing a license for me to work on this and other open-source projects.

[![](https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.svg)](https://www.jetbrains.com/?from=https://github.com/jiannei)

## Contributors ✨

Thanks goes to these wonderful people ([emoji key](https://allcontributors.org/docs/en/emoji-key)):

<!-- ALL-CONTRIBUTORS-LIST:START - Do not remove or modify this section -->
<!-- prettier-ignore-start -->
<!-- markdownlint-disable -->
<table>
  <tr>
    <td align="center"><a href="https://github.com/vanthao03596"><img src="https://avatars.githubusercontent.com/u/34786441?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Pham Thao</b></sub></a><br /><a href="#design-vanthao03596" title="Design">🎨</a></td>
    <td align="center"><a href="https://www.guanguans.cn"><img src="https://avatars.githubusercontent.com/u/22309277?v=4?s=100" width="100px;" alt=""/><br /><sub><b>guanguans</b></sub></a><br /><a href="https://github.com/jiannei/laravel-response/issues?q=author%3Aguanguans" title="Bug reports">🐛</a></td>
  </tr>
</table>

<!-- markdownlint-restore -->
<!-- prettier-ignore-end -->

<!-- ALL-CONTRIBUTORS-LIST:END -->

This project follows the [all-contributors](https://github.com/all-contributors/all-contributors) specification. Contributions of any kind welcome!

## Stargazers over time

[![Stargazers over time](https://starchart.cc/jiannei/laravel-response.svg)](https://starchart.cc/jiannei/laravel-response)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.