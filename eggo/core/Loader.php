<?php
declare(strict_types=1);

namespace Eggo\core;

class Loader
{
    public static $_loader;

    public static function init(): Loader
    {
        if (self::$_loader == NULL)
            self::$_loader = new self();
        return self::$_loader;
    }

    public function __construct()
    {
        spl_autoload_register(array($this, 'model'));
        spl_autoload_register(array($this, 'lib'));
        spl_autoload_register(array($this, 'controller'));
        spl_autoload_register(array($this, 'library'));
    }

    public function library($class)
    {
        set_include_path(get_include_path() . PATH_SEPARATOR . '/lib/');
        spl_autoload_extensions('.php');
        spl_autoload($class);
    }

    public function controller($class)
    {
        $class = preg_replace('/_controller$/ui', '', $class);
        set_include_path(get_include_path() . PATH_SEPARATOR . '/controller/');
        spl_autoload_extensions('.php');
        spl_autoload($class);
    }

    public function model($class)
    {
        $class = preg_replace('/_model$/ui', '', $class);
        set_include_path(get_include_path() . PATH_SEPARATOR . '/model/');
        spl_autoload_extensions('.php');
        spl_autoload($class);
    }

}
//call
// Autoloader::init ();