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
    'enum' => \Jiannei\Response\Laravel\Repositories\Enums\ResponseCodeEnum::class,

    'validation_error_code' => \Jiannei\Response\Laravel\Repositories\Enums\ResponseCodeEnum::HTTP_UNPROCESSABLE_ENTITY,
];
