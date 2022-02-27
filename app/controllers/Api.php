<?php

namespace App\controllers;

use Eggo\core\Constant;
use Eggo\core\Singleton;
use Eggo\core\SingletonMulti;
use Eggo\lib\GoConfig;
use Eggo\lib\GoFunc;
use Eggo\lib\GoIp;
use Eggo\core\Controller;

use Eggo\lib\GoMail;
use Eggo\lib\GoRequest;
use Exception;
use Phalcon\Kernel;
use stdClass;


class Api extends Controller
{
    use SingletonMulti;

    // use Singleton;

    //  protected static $res;
    protected static $id;

    final public function __construct()
    {
        parent::__construct();
        self::$id = hash('sha256', time());
    }

    public static function getId()
    {
        return self::$id;
    }

    /**
     * @throws Exception
     */
    public function index()
    {
        // Singleton::goSingle()::y();
        // $t = Func::getRequest();
        $data = ['a1111' => 1, 'b' => '111111'];
        $data1 = '{ "a": 1, "b": "test" }';
        $headers = ['Content-Type' => 'application/json'];
        $person = new stdClass;
        $person->name = 'ElePHPant ElePHPantsdotter';
        $person->website = 'https://php.net/elephpant.php';
        $person->headers = $headers;
        $params['url'] = 'http://192.168.0.250';
        $params['data'] = $data;
        $params['sync'] = 0;
        $params['headers'] = $headers;
        // $params['cookies'] = ['cookies' => 'cooke'];
        $params['method'] = 'POST';
        $params['contentType'] = 'json';

        $SendCode = array(
            'Email' => '42321851@qq.com',
            'EmailCode' => 2226,
            'EmailUser' => 'UserName',
            'Title' => '(系统自动邮件,请勿回复1)',
            'Content' => sprintf('尊敬的用户<b>您的验证码是：%s</b>,如非本人操作无需理会', 'xxx')
        );


        // $flag = GoMail::init()::send($SendCode);
        //  var_export($flag);
        $rr = GoConfig::init()::get('routes');
        //  var_dump($rr);
        $res = GoRequest::init()::get();
        // var_dump($res);

        GoFunc::init()::Log('aaatsss');


        // $n = new \App\middlewares\kernel();

        // $n = \App\middlewares\kernel::init();
        //  $b = \App\middlewares\kernel::init()::$debug;

        // var_export($a);


        $ip2region = GoIp::init();

        $ip = '61.140.232.170';
        echo PHP_EOL;
        echo "查询IP：{$ip}" . PHP_EOL;
//        $info = $ip2region::getBiSearch($ip);
//        var_export($info);

        //GoIp::Init()::getIP()
        echo PHP_EOL;
        $info = $ip2region::get($ip);
        var_export($info);
        echo PHP_EOL;

        $singleton = Api::init();
        echo $singleton::getId();
        echo PHP_EOL;
        $singleton2 = Api::init();
        echo $singleton2::getId();

        $singleton = Api::init();
        $b1 = unserialize(serialize($singleton));
        var_dump($singleton === $b1); //false
        var_dump($b1 === Api::init()); //false
        var_dump($singleton === Api::init()); //true

    }

    public function test()
    {
        echo 'api 方法test';
    }


}

