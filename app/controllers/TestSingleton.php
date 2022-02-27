<?php

namespace App\controllers;


use Eggo\core\SingletonMulti;

class TestSingleton
{
    use SingletonMulti;

    protected static $id;

    public function __construct()
    {
        self::$id = hash('sha256', time());
    }

    public static function getId()
    {
        return self::$id;
    }

    public function index()
    {
        echo '这里是测试控制器';
    }

    public function test()
    {
        $singleton0 = TestSingleton::init();

        echo $singleton0->getId();
        echo PHP_EOL;
        $singleton1 = TestSingleton::init();
        echo $singleton1->getId();
        echo '单例测试';

        $new = new TestSingleton();  // ×测试 克隆
        $clone = clone $singleton0;      //
        var_export($clone);

        $singleton2 = TestSingleton::init(); // 测试序列化
        $b1 = unserialize(serialize($singleton2));
        var_dump($singleton2 === $b1); //false
        var_dump($b1 === TestSingleton::init()); //false
        var_dump($singleton2 === TestSingleton::init()); //true

    }
}




