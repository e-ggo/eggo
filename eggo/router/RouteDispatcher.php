<?php

namespace Eggo\router;

use Generator;

/**
 * 原生路由实现方法 测试使用
 * @mail eggo.com.cn@gmail.com
 * @author Eggo <https://www.eggo.com.cn>
 * @date 2022-02-27 2:57
 */
class RouteDispatcher
{
    /**
     * @var array
     */
    private static $instance = [];
    /**
     * @var array|mixed|null
     */
    protected static $params = [];

    /**
     *
     * @author Eggo
     * @date 2022-02-27 2:57
     */
    private final function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * @param null $params
     */
    final protected function __construct($params = null)
    {
        self::$params = $params;
    }

    /**
     *
     * @param ...$args
     * @return $this|mixed
     * @author Eggo
     * @date 2022-02-27 2:57
     */
    final public static function init(...$args)
    {
        return static::$instance[static::class] ?? static::$instance[static::class] = new static(...$args);
    }

    /**
     *
     * @return mixed
     * @author Eggo
     * @date 2022-02-27 3:01
     */
    public static function run()
    {
        return self::LoadRoute(self::$params)->current();
    }

    /**
     *
     * @param array $params
     * @return Generator
     * @author Eggo
     * @date 2022-02-27 2:57
     */
    protected static function LoadRoute(array $params): Generator
    {
        $actionName = empty($params['defaultAction']) ? 'index' : $params['defaultAction'];
        $controllerName = empty($params['defaultController']) ? 'Index' : $params['defaultController'];
        $controllerNamespaces = empty($params['namespaces']) ? 'App\\controllers\\' : $params['namespaces'];
        $controllerSuffix = $params['controllerSuffix'];
        $vars = array();
        $uri = rawurldecode($_SERVER['REQUEST_URI']);
        if (false !== $pos = strpos($uri, '?')) $uri = substr($uri, 0, $pos);
        $uri = trim($uri, '/');
        if ($uri) {
            $uriArray = array_filter(explode('/', $uri));
            // 获取控制器名
            $controllerName = ucfirst($uriArray[0]);
            // 获取动作名
            array_shift($uriArray);
            $actionName = $uriArray ? $uriArray[0] : $actionName;
            // 获取URL参数
            array_shift($uriArray);
            $vars = $uriArray ?: array();
        }
        if (empty($controllerSuffix)) $controller = $controllerNamespaces . $controllerName;
        else $controller = $controllerNamespaces . $controllerName . 'Controller';

        if (!class_exists($controller) && !method_exists($controller, $actionName)) exit('控制器或方法不存在');
        $dispatch = new $controller();
        //yield call_user_func_array(array($dispatch, $actionName), $vars);
        yield $dispatch->$actionName($vars);
    }

    /**
     *
     * @param string $uri
     * @return string
     * @author Eggo
     * @date 2022-02-27 2:57
     */
    protected static function RequestUri(string $uri = ''): string
    {
        if (false !== $pos = strpos($uri, '?')) $uri = substr($uri, 0, $pos);
        // $uri = trim($uri, '/');
        $uri = trim(preg_replace('~/{2,}~', '/', $uri), '/');
        return $uri === '' ? '/' : "/{$uri}";
    }

}