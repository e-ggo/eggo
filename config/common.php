<?php
return [
    // 服务名称
    'app_name' => 'Eggo',
    // 时区的配置
    'default_timezone' => 'Asia/Shanghai',
    // 是否允许跨域
    'Allow_Origin' => true,
    // 设置php 最大运行内存的设置
    'memory_limit' => '512M',
    /* 控制器路径 */
    'namespaces' => 'App\\controllers\\',
    // 中间件
    'middlewares' => 'App\\middlewares\\',
    // 控制器扩展默认如(IndexController)格式 默认为空
    'controllerSuffix' => '',
    // cache|logs缓存与日志根目录
    'temp_dir' => 'runtime',
    // 应用运行FPM,CLI,SWooLe,WorkerMan [0为fast-route路由1为传统MVC路由模式]
    'running_mode' => 1,
    //默认控制器
    'defaultController' => 'Index',
    //默认控制器方法名称
    'defaultAction' => 'index',
    // 是否开启调试模式
    'debug' => true,
    // 网站根URL
    'APP_URL' => 'http://127.0.0.1/',
    /* 脚本目录名称 */
    'jobs_folder_name' => 'jobs',
];

