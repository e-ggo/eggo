<?php

namespace App\controllers;

//use Eggo\lib\Func;

use Eggo\lib\Func;

class IndexController
{
    public function index()
    {
        //echo '这里是测试控制器';
        Func::go(200,'xxx','aaa');
    }

    public function test()
    {
        echo '闭包路由测试';
    }
}