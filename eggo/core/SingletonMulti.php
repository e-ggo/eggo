<?php

namespace Eggo\core;

/**
 * trait
 * @mail eggo.com.cn@gmail.com
 * @author Eggo <https://www.eggo.com.cn>
 * @date 2022-02-22 23:43
 */
trait SingletonMulti
{
    private static $instance = [];

    final private function __construct()
    {
    }

    /**
     * Singletons should not be cloneable.
     */
    final private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * __sleep方法，将返回置空防止序列化反序列化获得新的对象
     * @return array
     */
    final protected function __sleep()
    {
        return [];
    }

//    final protected function __wakeup()
//    {
//    }

    /**
     *
     * @author Eggo
     * @date 2022-02-22 23:44
     */
    final public static function init(...$args)
    {
        return static::$instance[static::class] ?? static::$instance[static::class] = new static(...$args);
    }

}