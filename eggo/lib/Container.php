<?php


namespace Eggo\lib;

use Eggo\exception\ExceptionHandle;
use Exception;

/**
 * 依赖注入容器
 *
 * Dependency Injection Container
 *
 * 外观模式的典型代表
 *
 * @author TIERGB <https://github.com/TIGERB>
 */
class Container
{
    /**
     * 类映射
     *
     * class map
     *
     * @var array
     */
    private $classMap = [];

    /**
     * class instance 映射
     *
     * class instance map
     *
     * @var array
     */
    public $instanceMap = [];

    /**
     * 注入一个类
     *
     * inject a class
     *
     * @param string $alias 类别名
     * @param string $objectName 类名
     */
    public function set(string $alias = '', string $objectName = '')
    {
        $this->classMap[$alias] = $objectName;
        if (is_callable($objectName)) {
            return $objectName();
        }
        return new $objectName;
    }

    /**
     * 获取一个类的实例
     *
     * get a instance from a class
     *
     * @param string $alias 类名或别名
     * @return object
     * @throws Exception
     */
    public function get(string $alias = '')
    {
        if (array_key_exists($alias, $this->classMap)) {
            if (is_callable($this->classMap[$alias])) {
                return $this->classMap[$alias]();
            }
            if (is_object($this->classMap[$alias])) {
                return $this->classMap[$alias];
            }
            return new $this->classMap[$alias];
        }
        return ExceptionHandle::Handle('Class:' . $alias, 404);
    }

    /**
     * 注入一个单例类
     *
     * inject a singleton class
     *
     * @param string $alias 类名或别名
     * @param object||closure||string $object 实例或闭包或类名
     * @return object
     * @throws Exception
     */
    public function setSingle(string $alias = '', $object = '')
    {
        if (is_callable($alias)) {
            $instance = $alias();
            $className = get_class($instance);
            $this->instanceMap[$className] = $instance;
            return $instance;
        }
        if (is_callable($object)) {
            if (empty($alias)) {
                return ExceptionHandle::Handle(sprintf('%s is empty', $alias), 400);
            }
            if (array_key_exists($alias, $this->instanceMap)) {
                return $this->instanceMap[$alias];
            }
            $this->instanceMap[$alias] = $object;
        }
        if (is_object($alias)) {
            $className = get_class($alias);
            if (array_key_exists($className, $this->instanceMap)) {
                return $this->instanceMap[$alias];
            }
            $this->instanceMap[$className] = $alias;
            return $this->instanceMap[$className];
        }
        if (is_object($object)) {
            if (empty($alias)) {
                return ExceptionHandle::Handle(sprintf('%s is empty', $alias), 400);
            }
            $this->instanceMap[$alias] = $object;
            return $this->instanceMap[$alias];
        }
        if (empty($alias) && empty($object)) {
            return ExceptionHandle::Handle(sprintf('%s and %s is empty', $alias, $object), 400);
        }
        $this->instanceMap[$alias] = new $alias();
        return $this->instanceMap[$alias];
    }

    /**
     * 获取一个单例类
     *
     * get a singleton instance
     *
     * @param string $alias 类名或别名
     * @param string $closure 闭包
     * @return object
     * @throws Exception
     */
    public function getSingle(string $alias = '', string $closure = '')
    {
        if (array_key_exists($alias, $this->instanceMap)) {
            $instance = $this->instanceMap[$alias];
            if (is_callable($instance)) {
                return $this->instanceMap[$alias] = $instance();
            }
            return $instance;
        }
        if (is_callable($closure)) {
            return $this->instanceMap[$alias] = $closure();
        }
        return ExceptionHandle::Handle(sprintf('Class: %s', $alias), 404);
    }
}
