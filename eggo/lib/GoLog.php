<?php
declare(strict_types=1);

namespace Eggo\lib;

use Eggo\core\Constant;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\WebProcessor;


//Log::trace('DEBUG', [
//    'code' => "这里是一个错误日志",
//    'msg' => [//数组变量，可为空
//        'param1' => 'a',
//        'param2' => 'b'
//    ]
//],'error.log');
//   Log::trace('DEBUG', ['msg' => '200',''], 'error.log');
class GoLog
{
    static $log = null; // static app logger instance

    public static function trace(string $level = 'DEBUG', array $msg = [], string $file = '')
    {
        switch ($level) {
            case 'DEBUG':  // LOG类型
                $level = Logger::DEBUG;
                break;
            case 'INFO':
                $level = Logger::INFO;
                break;
            case 'NOTICE':
                $level = Logger::NOTICE;
                break;
            case 'WARNING':
                $level = Logger::WARNING;
                break;
            case 'ERROR':
                $level = Logger::ERROR;
                break;
            case 'CRITICAL':
                $level = Logger::CRITICAL; //严重错误
                break;
            default:
                $level = Logger::DEBUG;
                break;
        }
        // 创建日志频道

        $logger = new Logger('eggo_logger');
        $dateFormat = 'Y年n月d日 H:i:s';
        $format = '%datetime% > %level_name% > %message% %context% %extra%' . PHP_EOL;
        $formatter = new LineFormatter($format, $dateFormat);
        $stream = new StreamHandler(Constant::APP_LOG, $level);
        $stream->setFormatter($formatter);
        //$logger->pushHandler(new StreamHandler(ROOT_DIR . 'runtime/logs/' . $file, $level));
        $logger->pushHandler($stream);
        $logger->pushProcessor(new MemoryUsageProcessor);
        $logger->pushProcessor(new WebProcessor);
        $logger->pushHandler(new FirePHPHandler());
        $logger->info($msg['code'] ?? '', $msg['msg'] ?? []);
    }
}
