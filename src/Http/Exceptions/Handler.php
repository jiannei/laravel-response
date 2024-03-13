<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <jiannei@sinan.fun>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Http\Exceptions;

use Jiannei\Response\Laravel\Support\Traits\ExceptionTrait;

class Handler extends \Illuminate\Foundation\Exceptions\Handler
{
    use ExceptionTrait;
}
