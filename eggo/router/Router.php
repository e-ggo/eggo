<?php

namespace Eggo\router;


use Eggo\core\Constant;
use Eggo\exception\ExceptionHandle;
use Eggo\lib\GoConfig;
use Exception;
use Generator;


class Router
{
    /**
     * @var array
     */
    private static $_instance = [];

    /**
     * @var
     */
    protected static $routes;

    /**
     *
     * @author Eggo
     * @date 2022-02-27 1:48
     */
    final private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * @throws Exception
     */
    final protected function __construct($routes = null)
    {
        self::$routes = empty($routes) ? GoConfig::init(ROOT . 'config/routes.php')::get('routes') : $routes;
        if (empty(self::$routes)) ExceptionHandle::Handle('未找到路由配置');
    }

    /**
     *
     * @param ...$args
     * @return mixed|static
     * @throws Exception
     * @author Eggo
     * @date 2022-02-27 1:48
     */
    final public static function init(...$args)
    {
        return static::$_instance[static::class] ?? static::$_instance[static::class] = new static(...$args);
    }

    /**
     *
     * @throws Exception
     * @author Eggo
     * @date 2022-02-27 1:48
     */
    public static function run()
    {
        $routes = self::$routes;

        $routes = self::AppendRoute($routes);

        $handlers = function (\FastRoute\RouteCollector $r) use ($routes) {
            foreach ($routes as $route) {
                $r->addRoute($route[0], $route[1], $route[2]);
            }
        };

        if (true === Constant::ROUTER_CACHE) {
            // echo '打开缓存'; 修改路由routes文件一定清除缓存文件
            // if (!file_exists(Constant::ROUTER_CACHE_FILE)) touch(Constant::ROUTER_CACHE_FILE);
            $optionsCache = ['cacheFile' => Constant::ROUTER_CACHE_FILE, 'cacheDisabled' => false];
            $dispatcher = \FastRoute\cachedDispatcher($handlers, $optionsCache);
        } else $dispatcher = \FastRoute\simpleDispatcher($handlers);  // '无缓存模式';

        RouterHandler::init($dispatcher)::handle();

    }


    /**
     *
     * @param $routes
     * @return Generator
     * @author Eggo
     * @date 2022-02-27 1:48
     */
    protected static function AppendRoute($routes): Generator
    {
        foreach ($routes as $route) {
            $methods = array_map('strtoupper', (array)$route[0]);
            if (empty($methods) == 0) $methods = array('GET', 'POST');
            yield array($methods, $route[1], $route[2]);
        }
    }

}