<?php

use Illuminate\Support\Arr;

if (! function_exists('make')) {
    /**
     * @psalm-param string|array<string, mixed> $abstract
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    function make($abstract, array $parameters = [])
    {
        if (! in_array(gettype($abstract), ['string', 'array'])) {
            throw new \InvalidArgumentException(
                sprintf('Invalid argument type(string/array): %s.', gettype($abstract))
            );
        }

        if (is_string($abstract)) {
            return app($abstract, $parameters);
        }

        $classes = ['__class', '_class', 'class'];
        foreach ($classes as $class) {
            if (! isset($abstract[$class])) {
                continue;
            }

            $parameters = Arr::except($abstract, $class) + $parameters;
            $abstract = $abstract[$class];

            return make($abstract, $parameters);
        }

        throw new \InvalidArgumentException(
            sprintf('The argument of abstract must be an array containing a `%s` element.', implode('` or `', $classes))
        );
    }
}
