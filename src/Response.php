<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel;

use Jiannei\Response\Laravel\Contracts\Format;
use Jiannei\Response\Laravel\Support\Traits\JsonResponseTrait;

class Response
{
    use JsonResponseTrait;

    protected $formatter;

    public function __construct(Format $format)
    {
        $this->formatter = $format;
    }
}
