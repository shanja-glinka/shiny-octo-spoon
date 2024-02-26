<?php

namespace app\utils;


class Methods
{
    /**
     * @param string $methodName
     * @param array $args
     * @return void
     */
    public static function InstallMethod($methodName, $args = [])
    {
        if (!class_exists($methodName, true)) {
            throw new \RuntimeException("Class '$methodName' not found", 500);
        }

        if (is_array($args) and count($args) == 0) {
            return new $methodName;
        }

        if (!is_array($args)) {
            $args = [$args];
        }

        $ref = new \ReflectionClass($methodName);
        $instance = $ref->newInstanceWithoutConstructor();
        $constructor = $ref->getConstructor();
        $constructor->setAccessible(true);
        $constructor->invokeArgs($instance, $args);

        return $instance;
    }

    /**
     * @param object|string $method
     * @param string $methodName
     * @param array $args
     * @return void
     */
    public static function callMethod($method, $methodName, $args = [])
    {
        if (!method_exists($method, $methodName)) {
            $method = is_string($method) ? $method : $method::class;
            throw new \RuntimeException("Method '$method::$methodName' not found", 500);
        }

        if (!is_array($args)) {
            $args = [$args];
        }

        return call_user_func_array(array($method, $methodName), $args);
    }
}
