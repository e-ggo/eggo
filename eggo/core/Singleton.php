<?php

namespace Eggo\core;

/**
 * trait
 * @mail eggo.com.cn@gmail.com
 * @author Eggo <https://www.eggo.com.cn>
 * @date 2022-02-22 23:43
 */
trait Singleton
{
    private static $instance = null;

    final private function __construct()
    { /* ... @return Singleton */
    }

    final private function __clone()
    { /* ... @return Singleton */
    }

    final protected function __wakeup()
    { /* ... @return Singleton */
    }

    final public static function init()
    {
        if (null === static::$instance) static::$instance = new static();
        return static::$instance;
    }

    final public static function getInstance()
    {
        return static::$instance ?? static::$instance = new static();
    }


}
