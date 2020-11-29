<?php

/*
 * This file is part of the Jiannei/laravel-response.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Repositories\Enums;

use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Lang;
use Jiannei\Enum\Laravel\Contracts\LocalizedEnumContract;
use Jiannei\Enum\Laravel\Enum;
use ReflectionClass;
use ReflectionException;

class ResponseCodeEnum extends Enum implements LocalizedEnumContract
{
//    /**
//     * Get the description for an enum value.
//     *
//     * @param  mixed  $value
//     * @return string
//     */
//    public static function getDescription($value): string
//    {
//        return static::getLocalizedDescription($value) ?? HttpResponse::$statusTexts[$value];
//    }

    protected static function getLocalizedDescription($value): ?string
    {
        if (static::isLocalizable()) {
            $localizedStringKey = static::getLocalizationKey().'.'.$value;
            if (Lang::has($localizedStringKey)) {
                return Lang::get($localizedStringKey);
            }
        }

        return HttpResponse::$statusTexts[$value] ?? null;
    }

    /**
     * Get all of the constants defined on the class.
     *
     * @return array
     * @throws ReflectionException
     */
    protected static function getConstants(): array
    {
        $calledClass = static::class;
        if (! array_key_exists($calledClass, static::$cache)) {
            $reflect = new ReflectionClass($calledClass);
            static::$cache[$calledClass] = array_merge(self::getHttpConstants(), $reflect->getConstants());
        }

        return static::$cache[$calledClass];
    }

    protected static function getHttpConstants(): array
    {
        $reflect = new ReflectionClass(HttpResponse::class);

        return $reflect->getConstants();
    }
}
