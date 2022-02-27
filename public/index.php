<?php
declare(strict_types=1);

use Eggo\App;

define('ROOT_DIR', dirname(__DIR__) . DIRECTORY_SEPARATOR);
// 加载框架文件
require_once(ROOT_DIR . 'vendor/autoload.php');
require_once(ROOT_DIR . 'eggo/App.php');
// 实例化加载路由配置
try {
    App::Context()::Run();
} catch (Exception $e) {
    echo $e->getMessage() . '<br>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}





