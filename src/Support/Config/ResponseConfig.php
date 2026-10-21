<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <jiannei@sinan.fun>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Support\Config;

use Jiannei\Response\Laravel\Support\Format;

class ResponseConfig
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(protected array $config = []) {}

    /**
     * Field display and alias rules for the response envelope.
     *
     * @return array<string, mixed>
     */
    public function fields(): array
    {
        $format = $this->config['format'] ?? [];
        $fields = is_array($format) ? ($format['config'] ?? []) : [];

        return is_array($fields) ? $fields : [];
    }

    /**
     * The HTTP status override for failed responses, or false to keep the business code.
     */
    public function errorCodePolicy(): int|false
    {
        $errorCode = $this->config['error_code'] ?? false;

        return is_int($errorCode) && $errorCode > 0 ? $errorCode : false;
    }

    public function locale(): string
    {
        $locale = $this->config['locale'] ?? 'enums';

        return is_string($locale) && $locale !== '' ? $locale : 'enums';
    }

    /**
     * The exception rule for an exact exception class.
     *
     * Returns false when the exception is explicitly opted out, an array when a
     * rule exists, and an empty array when there is no rule.
     *
     * @return array<string, mixed>|false
     */
    public function exceptionRule(string $class): array|false
    {
        $rules = $this->config['exception'] ?? [];

        if (! is_array($rules) || ! array_key_exists($class, $rules)) {
            return [];
        }

        $rule = $rules[$class];

        if ($rule === false) {
            return false;
        }

        return is_array($rule) ? $rule : [];
    }

    /**
     * @return class-string
     */
    public function formatterClass(): string
    {
        $format = $this->config['format'] ?? [];
        $class = is_array($format) ? ($format['class'] ?? Format::class) : Format::class;

        return is_string($class) && $class !== '' ? $class : Format::class;
    }
}
