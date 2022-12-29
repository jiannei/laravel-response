<?php

namespace Jiannei\Response\Laravel\Tests\Support;

use Illuminate\Support\Facades\Config;

class Format extends \Jiannei\Response\Laravel\Support\Format
{
    public function data(?array $data, ?string $message, int $code, $errors = null): array
    {
        if (! $message && class_exists($enumClass = Config::get('response.enum'))) {
            $message = $enumClass::fromValue($code)->description;
        }

        return $this->formatDataFields([
            'status' => $this->formatStatus($code),
            'code' => $code,
            'message' => $message,
            'data' => $data ?: (object) $data,
            'error' => $errors ?: (object) [],
            'extra' => [
                'time' => time()
            ],
        ], Config::get('response.format.fields', []));
    }
}