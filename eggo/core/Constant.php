<?php

namespace Eggo\core;
const DS = DIRECTORY_SEPARATOR;
class Constant
{
    // info 信息可配置
    public static $INFO = true;
    // 打开debug 调试模式
    const DEBUG = true;
    // 应用运行FPM, CLI, SWooLe ,WorkerMan [0为fast-route路由1为传统路由模式]
    const RUN_MODE = 0;
    // Router Cache 是否开始路由缓存
    const ROUTER_CACHE = false;
    // 服务名称
    const APP_NAME = 'Eggo';
    // 时区的配置
    const DEFAULT_TIMEZONE = 'Asia/Shanghai';
    // 是否允许跨域
    const AllOW_ORIGIN = true;
    // 设置php 最大运行内存的设置
    const MEMORY_LIMIT = '512M';
    // 日志与缓存主目录
    const RUNTIMES = ROOT . 'runtimes' . DS;
    // Cache 存放缓存目录
    const CACHE_DIR = self::RUNTIMES . 'cache' . DS;
    // Logs 存入日志目录
    const LOGS_DIR = self::RUNTIMES . 'logs' . DS;
    // 路由缓存文件
    const ROUTER_CACHE_FILE = self::CACHE_DIR . 'cache.route';
    // 数据库缓存文件
    const DB_CACHE_FILE = self::CACHE_DIR . 'cache.db';
    // 视图文件缓存
    const VIEW_CACHE_FILE = self::CACHE_DIR . 'cache.view';
    // 发送http请求日志目录
    const HTTP_REQUEST_LOG = self::LOGS_DIR . 'http_log' . DS;
    // 系统应用日志目录
    const APP_LOG = self::LOGS_DIR . 'app_log' . DS;
    // debug 日志目录
    const DEBUG_LOG = self::LOGS_DIR . 'debug_log' . DS;

    // HTTP 代码
    const CODE_OK = 200;
    // Redirection 3xx
    const CODE_MULTIPLE_CHOICES = 300;
    const CODE_MOVED_PERMANENTLY = 301;
    const CODE_MOVED_TEMPORARILY = 302;
    const CODE_SEE_OTHER = 303;
    const CODE_NOT_MODIFIED = 304;
    // Client Error 4xx
    const CODE_BAD_REQUEST = 400;
    const CODE_UNAUTHORIZED = 401;
    const CODE_PAYMENT_REQUIRED = 402;
    const CODE_FORBIDDEN = 403;
    const CODE_NOT_FOUND = 404;
    const CODE_METHOD_NOT_ALLOWED = 405;
    // Server Error 5xx
    const CODE_INTERNAL_SERVER_ERROR = 500;
    const CODE_NOT_IMPLEMENTED = 501;
    const CODE_BAD_GATEWAY = 502;


}