<?php
declare(strict_types=1);

namespace Eggo\core;

use Eggo\lib\GoRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 控制器基类
 */
abstract class Controller
{
    protected $_controller;
    protected $_action;
    protected $_view;
    protected $request;
    protected $response;
    //  use SingletonMulti;
    protected static $res;

    // 构造函数，初始化属性，并实例化对应模型

    public function __construct()
    {
       // self::$res = GoRequest::getInstance()::getParams(0);
    }

    public static function aaa()
    {
       // return self::$res;
    }

    // 分配变量
    public function assign($name, $value)
    {
        $this->_view->assign($name, $value);
    }

    // 渲染视图
    public function render()
    {
        $this->_view->render();
    }
}