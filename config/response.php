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
    'enum' => \Jiannei\Enum\Laravel\Repositories\Enums\HttpStatusCodeEnum::class,

    'error_code' => \Jiannei\Enum\Laravel\Repositories\Enums\HttpStatusCodeEnum::HTTP_INTERNAL_SERVER_ERROR,

    'validation_error_code' => \Jiannei\Enum\Laravel\Repositories\Enums\HttpStatusCodeEnum::HTTP_UNPROCESSABLE_ENTITY,

    'format' => [
        'paginated_resource' => [
            'data_field' => 'data',
        ],
    ],
];
