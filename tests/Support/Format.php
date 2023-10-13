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
    protected function formatDataFields(array $data): array
    {
        $data = parent::formatDataFields($data);

        return array_merge($data, [
            'extra' => [
                'time' => time(),
            ],
        ]);
    }
}
