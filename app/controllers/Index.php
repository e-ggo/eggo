<?php

namespace App\controllers;


use Eggo\App;
use Eggo\core\Controller;
use Eggo\lib\GoConfig;
use Eggo\lib\GoFunc;
use Eggo\lib\GoRequest;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Index extends Controller
{
    protected $request;
    protected $response;

//    public function __construct()
//    {
//        parent::__construct($controller, $action, Request $request, Response $response);
//    }


    public function index()
    {
        $content = GoRequest::init()::get(1);

        $json = json_encode($content);

        // echo $json;
//var_dump(ROOT_DIR);
        GoFunc::Log($json);


        GoFunc::init()::output(200, 'xxxxx', $content);


//        GoFunc::Log($content);
//        var_dump($content);
        // $Params = trim(file_get_contents('php://input'));
        //  Func::Log($Params,'eggo');
        // return json_encode(['msg'=>'success']);
        //   ['msg'=>'success'];
        //    echo 'suc';

        // exit(json_encode(['code' => 200, 'status' => 1, 'url' => 'https://eggo.com.cn',
        //   'sync'=>'测试同步数据'], 448));


        //  GoFunc::go(200, 'tes22t', 'sss');
    }

    public function index1()
    {
        echo '这里是测试控制器';
    }

    public function test()
    {
        echo '闭包路由测试';
        $a = GoConfig::init(ROOT . 'common')::get('common');
        var_export($a);
    }

    public function show()
    {
        $content = '<h1>Hello World</h1>';
        $content .= 'Hello ' . $this->request->getParameter();
        $this->response->setContent($content);
    }

}




