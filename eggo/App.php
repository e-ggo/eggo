<?php
declare(strict_types=1);

namespace Eggo;
const DS = DIRECTORY_SEPARATOR;
defined('CORE_PATH') or define('CORE_PATH', __DIR__ . DS);
defined('ROOT') or define('ROOT', dirname(__DIR__) . DS);
defined('APP_DIR') or define('APP_DIR', dirname(__DIR__) . DS . 'app' . DS);

use Eggo\core\Constant;
use Eggo\lib\GoConfig;
use Eggo\router\RouteDispatcher;
use Eggo\router\Router;
use Exception;

/**
 * @mail eggo.com.cn@gmail.com
 * @author Eggo <https://www.eggo.com.cn>
 * @date 2022-02-23 18:37
 */
class App
{
    /**
     * @var null
     */
    private static $_instance = null;
    /**
     * @var array|GoConfig|mixed
     */
    protected static $config = array();

    /**
     * Singletons should not be cloneable.
     */
    final private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * 完全单例模式加载APP应用
     * 初始化加载框架全部类
     * 初始化加载所有目录下配置文件
     */
    final protected function __construct()
    {
        spl_autoload_register(array(self::class, 'Autoloader'));
        self::$config = GoConfig::init(ROOT . 'config');
    }

    /**
     *
     * @return App|null
     * @author Eggo
     * @date 2022-02-25 21:25
     */
    final public static function Context(): ?App
    {
        if (null === self::$_instance) {
            self::$_instance = new static();
        }
        return self::$_instance;
    }

    /**
     *
     * @throws Exception
     * @author Eggo
     * @date 2022-02-25 21:25
     */
    public static function Run()
    {
        self::SetReporting();
        self::SetHeaders();
        self::SetDbConfig();
        self::RegisterRoute();
    }


    /**
     *
     * @throws Exception
     * @author Eggo
     * @date 2022-02-25 21:25
     */
    protected static function RegisterRoute()
    {
        switch (Constant::RUN_MODE) {
            case 0: // 运行为fast-route模式支持控制器多级目录
                $routes = self::$config::get('routes');
                Router::init($routes)::run();
                break;
            case 1: // 运行为传统mvc路由模式不支持控制器多级目录
                echo 1;
                $params = self::$config::get('common');
                RouteDispatcher::init($params)::run();
                break;
        }
    }

    /**
     *   获取配置环境
     */
    protected static function SetReporting()
    {
        if (Constant::DEBUG) {
            $date = date('Y-m-d');
            $errLog = Constant::DEBUG_LOG . $date . '_app_error.log';
            ini_set('display_startup_errors', 'On'); // php启动错误信息
            ini_set('display_errors', 'On'); // 错误信息
            ini_set('log_errors', 'On');
            ini_set('error_log', $errLog);
            error_reporting(-1); // 打印出所有的错误信息
        }
        // 设置系统时区
        date_default_timezone_set(Constant::DEFAULT_TIMEZONE);
        // 设置php 最大运行内存的设置
        ini_set('memory_limit', Constant::MEMORY_LIMIT);

        if (session_status() == PHP_SESSION_NONE) session_start();
        if (version_compare(PHP_VERSION, '7.1.0', '<')) {
            throw new \InvalidArgumentException('PHP环境不能低于7.4.0,建议使用PHP7.4-8.1');
        }
    }

    /**
     *  配置数据库信息
     */
    protected static function SetDbConfig()
    {
        $db = self::$config::get('database')['db'];
        if ($db) {
            define('DB_HOST', $db['dbHost']);
            define('DB_USER', $db['dbUser']);
            define('DB_PASS', $db['dbPass']);
            define('DB_NAME', $db['dbName']);
            define('DB_PREFIX', $db['dbPrefix']);
            define('DB_TYPE', $db['dbType']);
            define('DB_MYSQLI', $db['dbMysqli']);
            define('DB_PORT', $db['dbPort']);
            define('DB_CHAR', $db['dbChar']);
        }
    }

    // 设置环境
    protected static function SetHeaders()
    {
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:*');
        header('Access-Control-Max-Age: 1000');
        // header('Access-Control-Allow-Headers:Content-Type, Authorization, X-Requested-With, token, ApiAuth, User-Agent, Keep-Alive, Origin, No-Cache, If-Modified-Since, Pragma, Last-Modified, Cache-Control, Expires, X-E4M-With');
        header('Access-Control-Allow-Credentials:true');
        header('Content-Type: text/html; charset=utf-8');
        if (Constant::AllOW_ORIGIN) {
            if (array_key_exists('HTTP_ACCESS_CONTROL_REQUEST_HEADERS', $_SERVER)) {
                header('Access-Control-Allow-Headers: ' . $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']);
            } else header('Access-Control-Allow-Headers: *');
        }
        if ('OPTIONS' == $_SERVER['REQUEST_METHOD']) exit(0);
    }

    // 自动加载框架类
    protected static function Autoloader($className)
    {
        $classMap = self::ClassMap();
        if (isset($classMap[$className])) {
            $file = $classMap[$className];
        } elseif (strpos($className, '\\') !== false) {
            $file = ROOT . str_replace('\\', '/', $className) . '.php';   //应用(根目录)文件
            if (!is_file($file)) return;
        } else return;
        require_once $file;
    }

    // 内核文件命名空间映射关系
    protected static function ClassMap(): array
    {
        return [
            'Eggo\exception\ExceptionHandle' => CORE_PATH . 'exception/ExceptionHandle.php',
            'Eggo\core\Controller' => CORE_PATH . 'core/Controller.php',
            'Eggo\core\View' => CORE_PATH . 'core/View.php',
            'Eggo\core\Singleton' => CORE_PATH . 'core/Singleton.php',
            'Eggo\core\SingletonMulti' => CORE_PATH . 'core/SingletonMulti.php',
            'Eggo\lib\GoFunc' => CORE_PATH . 'lib/GoFunc.php',
            'Eggo\lib\GoConfig' => CORE_PATH . 'lib/GoConfig.php'
        ];
    }


}