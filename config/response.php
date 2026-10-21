<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Jiannei\Response\Laravel\Support\Format;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <jiannei@sinan.fun>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Set the http status code when the response fails
    |--------------------------------------------------------------------------
    |
    | the reference options are false, 200, 500
    |
    | false, stricter http status codes such as 404, 401, 403, 500, etc. will be returned
    | 200, All failed responses will also return a 200 status code
    | 500, All failed responses return a 500 status code
    |
    | Successful responses always keep the first three digits of their business code.
    */

    'error_code' => false,

    // lang/zh_CN/enums.php
    'locale' => 'enums', // enums.\Jiannei\Enum\Laravel\Support\Enums\HttpStatusCode::class

    //  You can set some attributes (eg:code/message/error/header/options) for the exception, and it will override the default attributes of the exception
    'exception' => [
        ValidationException::class => [
            'code' => 422,
        ],
        AuthenticationException::class => [
        ],
        NotFoundHttpException::class => [
            'message' => '',
        ],
        ModelNotFoundException::class => [
            'message' => '',
        ],
    ],

    // Any key that returns data exists supports custom aliases and display.
    'format' => [
        'class' => Format::class, // must extend Jiannei\Response\Laravel\Support\Format
        'config' => [
            // key => config
            'status' => ['alias' => 'status', 'show' => true],
            'code' => ['alias' => 'code', 'show' => true],
            'message' => ['alias' => 'message', 'show' => true],
            'error' => ['alias' => 'error', 'show' => true],
            'data' => ['alias' => 'data', 'show' => true],
            'data.data' => ['alias' => 'data.data', 'show' => true], // e.g. data.rows, data.items, data.list
        ],
    ],
];
