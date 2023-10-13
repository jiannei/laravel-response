<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Tests\Support;

class Format extends \Jiannei\Response\Laravel\Support\Format
{
    public function data(mixed $data, ?string $message, int|\BackedEnum $code, $errors = null, $from = 'success'): array
    {
        return [
            'status' => $this->formatStatus($code),
            'code' => $code,
            'message' => $this->formatMessage($code, $message),
            'data' => $data ?: (object) $data,
            'error' => $errors ?: (object) [],
            'extra' => [
                'time' => time(),
            ],
        ];
    }
}
