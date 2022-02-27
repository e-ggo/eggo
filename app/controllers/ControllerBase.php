<?php

namespace App\controllers;

use Eggo\core\Controller;

class ControllerBase extends Controller
{
    public function initialize()
    {
        echo '控制器初始化';
    }

    /**
     * loadSource
     */
    public function loadSource()
    {
        echo '统一加载资源控制';
    }

}