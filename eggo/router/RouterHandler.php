<?php
declare(strict_types=1);

namespace Eggo\router;

use Eggo\exception\ExceptionHandle;
use Exception;
use Generator;
use FastRoute\Dispatcher;

/**
 * 单例模式运行路由
 * @mail eggo.com.cn@gmail.com
 * @author Eggo <https://www.eggo.com.cn>
 * @date 2022-02-23 17:28
 */
class RouterHandler
{
    private static $instance = [];
    /**
     * @var Dispatcher
     */
    protected static $dispatcher;

    /**
     * @param Dispatcher $dispatcher
     */
    final protected function __construct(Dispatcher $dispatcher)
    {
        self::$dispatcher = $dispatcher;
    }

    final public static function init(...$args)
    {
        return static::$instance[static::class] ?? static::$instance[static::class] = new static(...$args);
    }

    /**
     * @throws Exception
     */
    public static function handle()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = rawurldecode($_SERVER['REQUEST_URI']);
        $uri = self::RequestUri($uri);

        $routeInfo = self::$dispatcher->dispatch(strtoupper($method), $uri);
        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                ExceptionHandle::Handle(sprintf('Not found [%s]', $uri), 404);
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                $message = '405 Allowed methods: ' . implode(',', $allowedMethods);
                ExceptionHandle::Handle($message, 405);
                break;
            case \FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1]; // 路由控制器与方法名IndexController@index
                $vars = $routeInfo[2];    // 路由参数
                self::CallHandler($handler, $vars)->current();
                break;
            default:
                ExceptionHandle::Handle('No route info found');
                break;
        }
    }

    protected static function RequestUri(string $uri = ''): string
    {
        if (false !== $pos = strpos($uri, '?')) $uri = substr($uri, 0, $pos);
        // $uri = trim($uri, '/');
        $uri = trim(preg_replace('~/{2,}~', '/', $uri), '/');
        return $uri === '' ? '/' : "/{$uri}";
    }

    /**
     * @param $handler
     * @param $vars
     * @return Generator
     */
    protected static function CallHandler($handler, $vars): Generator
    {
        $call = [];
        if (is_callable($handler)) {
            switch ($handler) {
                case is_array($handler):
                    $controllerName = $handler[0];
                    $actionName = $handler[1];
                    if (!class_exists($controllerName) && !method_exists($controllerName, $actionName)) exit('控制器或方法不存在');
                    $call = call_user_func_array(array(new $controllerName(), $actionName), $vars);
                    break;
                case is_object($handler):
                    $call = count($vars) > 0 ? call_user_func_array($handler, $vars) : call_user_func_array($handler, array());
                    break;
            }
        } else if (is_string($handler) && strpos($handler, '@') !== false) {
            list($controllerName, $actionName) = explode('@', $handler, 2);
            if (!class_exists($controllerName) && !method_exists($controllerName, $actionName)) exit('控制器或方法不存在');
            $call = call_user_func_array(array(new $controllerName(), $actionName), $vars);
            // $call = call_user_func(array(new $controllerName(), $actionName), $vars);
        }
        yield $call;
    }


}