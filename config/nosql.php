<?php

/**
 * nosql相关配置
 */
return [
    // 需要提供支持的nosql种类 关系型数据库
    // 参数示例：redis,memcached,mongoDB
    'nosql' => 'support',
    // redis
    'redis' => [
        // 默认host
        'host' => '127.0.0.1',
        // 默认端口
        'port' => 'port',
        // 密码
        'password' => 'password',
    ],
    // memcached
    'memcached' => [
        'cache_id' => 'eggo',
        // 默认host
        'host' => '127.0.0.1',
        // 默认端口
        'port' => 11211,
        'weight' => 100,
        // 密码
        'password' => 'password'
    ],
    // mongoDB
    'mongoDB' => [
        // 默认host
        'host' => 'host',
        // 默认端口
        'port' => 'port',
        // 数据库名称
        'database' => 'database',
        // 用户名
        'username' => 'username',
        // 密码
        'password' => 'password',
    ]
];
