<?php

/*
 * This file is part of the Jiannei/laravel-response.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
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
    */

    'error_code' => false,

    //  You can set some attributes (eg:code/message/header/options) for the exception, and it will override the default attributes of the exception
    'exception' => [
        \Illuminate\Validation\ValidationException::class => [
            'code' => 422,
        ],
        \Illuminate\Auth\AuthenticationException::class => [

        ],
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class =>[
            'message' => ''
        ],
        \Illuminate\Database\Eloquent\ModelNotFoundException::class => [
            'message' => ''
        ],
    ],

    // Set the structure of the paging data return,the following structure will be returned by default,
    // You can modify the name of the inner data field through the following configuration items, such as rows or list
    //{
    //    "status": "success",
    //    "code": 200,
    //    "message": "Success.",
    //    "data": {
    //    "data": [
    //        // ...
    //    ],
    //        "meta": {
    //        // ...
    //    }
    //    },
    //    "error": {}
    //}

    'format' => [
        'fields' => [
            'code' => ['alia' => 'code', 'show' => true],
            'message' => ['alia' => 'message', 'show' => true],
            'data' => ['alia' => 'data', 'show' => true],
            'error' => ['alia' => 'error', 'show' => true],
        ],

        'paginated_resource' => [
            'data_field' => 'data',
        ],
    ],

    // You can use enumerations to define the code when the response is returned,
    // and set the response message according to the locale
    //
    // The following two enumeration packages are good choices
    //
    // https://github.com/Jiannei/laravel-enum
    // https://github.com/BenSampo/laravel-enum

    'enum' => '', // \Jiannei\Enum\Laravel\Repositories\Enums\HttpStatusCodeEnum::class
];
