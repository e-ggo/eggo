<?php
declare(strict_types=1);

namespace Eggo\core;

/**
 * 视图基类
 */
class View
{
    protected $variables = array();
    protected $_controller;
    protected $_action;

    function __construct($controller, $action)
    {
        $this->_controller = strtolower($controller);
        $this->_action = strtolower($action);
    }

    // 分配变量
    public function assign($name, $value)
    {
        $this->variables[$name] = $value;
    }

    // 渲染显示
    public function render()
    {
        extract($this->variables);
        $defaultHeader = ROOT_DIR . 'app/views/header.php';
        $defaultFooter = ROOT_DIR . 'app/views/footer.php';

        $controllerHeader = ROOT_DIR . 'app/views/' . $this->_controller . '/header.php';
        $controllerFooter = ROOT_DIR . 'app/views/' . $this->_controller . '/footer.php';
        $controllerLayout = ROOT_DIR . 'app/views/' . $this->_controller . '/' . $this->_action . '.php';

        // 页头文件
        if (is_file($controllerHeader)) {
            include($controllerHeader);
        } else {
            include($defaultHeader);
        }

        //判断视图文件是否存在
        if (is_file($controllerLayout)) {
            include($controllerLayout);
        } else {
            echo "<h1>无法找到视图文件</h1>";
        }

        // 页脚文件
        if (is_file($controllerFooter)) {
            include($controllerFooter);
        } else {
            include($defaultFooter);
        }
    }
}
