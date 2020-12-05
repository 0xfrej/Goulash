<?php

namespace Goulash\Support\Functor;

use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use RuntimeException;

// TODO: Maybe strict type checks?
//  Test accessibility violations
class Functor
{
    /**
     * Map parameter bag to class method and invoke
     * If method is constructor new object will be created and returned
     *
     * @param string|object $class Fully qualified class namespace or object
     * @param string $method Method name
     * @param array $bag Parameter bag
     * @param bool $array Indicate if parameter bag is array of parameter bags
     *
     * @return object|object[] New `object` or `array` of new `objects`
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public static function mapMethod($class, string $method, array $bag, bool $array = false)
    {
        $reflection = new ReflectionMethod($class, $method);
        $params     = $reflection->getParameters();

        if (! $array) {
            $bag = [$bag];
        }

        $results = [];
        foreach ($bag as $b) {
            if ($reflection->isConstructor()) {
                $results[] = new $class(...self::mapper($params, $b));
            }
            if ($reflection->isStatic()) {
                $results[] = $class::$method(...self::mapper($params, $b));
            }
            if(is_object($class)) {
                $results[] = $reflection->invokeArgs(...self::mapper($params, $b));
            }
            else {
                throw new RuntimeException("Method {$method} has to be of type static, non-static or constructor.");
            }
        }

        if ($array) {
            return $results;
        }
        return $results[0];
    }

    /**
     * Map parameter bag to function and invoke
     *
     * @param callable $function
     * @param array $bag Parameter bag
     * @param bool $array Indicate if parameter bag is array of parameter bags
     *
     * @return mixed|mixed[] Result or `array` of results
     * @throws ReflectionException
     */
    public static function mapFunc(callable $function, array $bag, bool $array = false)
    {
        $reflection = new ReflectionFunction($function);
        $params     = $reflection->getParameters();

        if (! $array) {
            $bag = [$bag];
        }

        $results = [];
        foreach ($bag as $b) {
            $results[] = $reflection->invokeArgs(...self::mapper($params, $b));
        }

        if ($array) {
            return $results;
        }
        return $results[0];
    }

    /**
     * @param ReflectionParameter[] $parameters Method parameters
     * @param mixed[] $bag Parameter bag to be mapped into method
     *
     * @return array Prepared array of parameters
     */
    protected static function mapper(array $parameters, array $bag): array
    {
        $mapped = [];

        foreach ($parameters as $param) {
            $name = $param->getName();

            if (! array_key_exists($name, $bag)) {
                if (! $param->isOptional()) {
                    throw new RuntimeException("Required parameter {$name} is missing in parameter bag!");
                }
                // TODO: Test how this will behave when there are multiple optional parameters and one is missing
                //  Maybe refactor to automatically infer missing optional parameters if there are more
                continue;
            }

            $mapped[$param->getPosition()] = $bag[$name];
        }

        return $mapped;
    }
}
