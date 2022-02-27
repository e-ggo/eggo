<?php

namespace Eggo\lib;

use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @mail eggo.com.cn@gmail.com
 * @author Eggo <https://www.eggo.com.cn>
 * @date 2022-02-23 15:26
 */
class GoRequest
{
    private static $instance = [];
    /**
     * @var int
     */
    protected static $timeout = 10;
    /**
     * @var int
     */
    protected static $connectTimeout = 10;
    /**
     * @var Client
     */
    protected static $httpClient;

    /**
     * @var Request
     */
    protected static $request;
    /**
     * @var
     */
    protected static $response;

    /**
     *
     * @author Eggo
     * @date 2022-02-26 14:18
     */
    final private function __clone()
    {

    }

    /**
     * @param Request|null $request
     */
    final private function __construct(Request $request = null)
    {
        // HttpFoundation Request
        self::$request = $request ?? Request::createFromGlobals();
        // GuzzleHttp Request
        self::$httpClient = new Client([
            'timeout' => self::$timeout,                      // Response timeout
            'connect_timeout' => self::$connectTimeout,       // Connection timeout
            'verify' => false,                                // Https cert
        ]);
    }

    /**
     *
     * @param ...$args
     * @return mixed|static
     * @author Eggo
     * @date 2022-02-26 13:34
     */
    final public static function init(...$args)
    {
        return static::$instance[static::class] ?? static::$instance[static::class] = new static(...$args);
    }

    /**
     *
     * 获取 所有 GET POST 参数
     * @param int $type
     * @param false $obj
     * @return mixed|string
     * @author Eggo
     * @date 2022-02-23 15:24
     */
    public static function get(int $type = 0, bool $obj = false)
    {
        switch ($type) {
            case 0:
                return self::getHttpRequest($obj)->current();
            case 1:
                return self::getRequest($obj)->current();
        }
        return $type;
    }

    /**
     *
     * http 获取 json get post 数据已转换数组接收 可转对象 true
     * @param false $obj
     * @return Generator
     * @author Eggo
     * @date 2022-02-23 15:24
     */
    protected static function getHttpRequest(bool $obj = false): Generator
    {
        $Params = self::$request->getContent();
        if ($Params) $Data = @json_decode($Params, true);
        if (!isset($Data)) $Data = array_merge(self::$request->query->all(), self::$request->request->all());
        if ($obj) yield json_decode(json_encode($Data)); else {
            yield $Data;
        }
    }

    /**
     *
     * 原生获取 json get post 数据已转换数组接收 可转对象 true
     * @param false $obj
     * @return Generator
     * @author Eggo
     * @date 2022-02-23 15:24
     */
    protected static function getRequest(bool $obj = false): Generator
    {
        $Params = trim(file_get_contents('php://input'));
        if ($Params) $Data = @json_decode($Params, true);
        if (!isset($Data)) $Data = array_merge(@$_GET, @$_POST);
        if ($obj) yield json_decode(json_encode($Data)); else {
            yield $Data;
        }
    }


    /**
     *
     * http url 重定向方法
     * @param string $url
     * @param array $params
     * @author Eggo
     * @date 2022-02-23 15:24
     */
    public static function HttpRedirect(string $url, array $params)
    {
        $http_url = self::buildUrl($url, $params)->current();
        $http_url = new RedirectResponse($http_url);
        $http_url->send();
        exit(0);
        // exit(json_encode(['code' => 200, 'status' => 1, 'url' => $http_url], 448)); // 生成可返回url数据接口
    }

    /**
     *
     * header 原生重定向方法
     * @param string $url
     * @param array $params
     * @author Eggo
     * @date 2022-02-23 15:24
     */
    public static function HeaderRedirect(string $url, array $params)
    {
        $http_url = self::buildUrl($url, $params)->current();
        header(sprintf('location:  %s', $http_url));
        exit(0);
        // exit(json_encode(['code' => 200, 'status' => 1, 'url' => $http_url], 448)); // 生成可返回url数据接口
    }


    /**
     * 生成 url
     * @param string $url
     * @param array $params
     * @return Generator
     * @author Eggo
     * @date 2022-02-23 15:24
     */
    protected static function buildUrl(string $url, array $params): Generator
    {
        if (empty($url)) exit;
        if (!empty($params)) {
            $str = http_build_query($params);
            yield $url . (stripos($url, '?') === false ? '?' : '&') . $str;
        } else yield $url;
    }

    // GuzzleHttp GET POST 同步异步发送数据请求
    // $url 发送地址 $params 发送参数 $headers 发送响应头 $cookies 发送cookies
    // $contentType 发送数据的格式 form_params【application/x-www-form-urlencoded】 json 为发送json格式
    // $httpType HTTP发送的请求的类型sync 0为异步发送 1为同步发送 默认POST异步发送数据
    // 函数 doRequest(array) 数组对象发送参数格式:(url为地址|data为数据|method默认POST为方法|headers为头|cookies|contentType为数据类型如果是json)
    // ['url' => 'http','data'=>'$data','method'=>'POST','headers'=>'$headers','cookies'=>'$cookies','sync'=>0]
    // 函数发字符串格式发送 send(url,data,headers,cookies,contentType,method,httpType[0异步|1同步])

    /**
     *
     * @param ...$args
     * @return mixed|string|void
     * @author Eggo
     * @date 2022-02-24 22:15
     */
    public static function doRequest(...$args)
    {
        $map = [];
        $HttpData = json_decode(json_encode($args, 320), true);
        array_map(function ($value) use (&$map) {
            $map = $value;
        }, $HttpData);

        if (is_array($map)) {
            if (empty($map['url'])) exit();
            $url = $map['url'];
            $data = empty($map['data']) ? ($map['data'] = '') : $map['data'];
            $method = empty($map['method']) ? ($map['method'] = 'POST') : $map['method'];
            $contentType = empty($map['contentType']) ? ($map['contentType'] = 'form_params') : $map['contentType'];

            $params = strtoupper($method) == 'GET' ? ['query' => $data] : [strtolower($contentType) => $data];
            if (!empty($map['headers'])) $params['headers'] = $map['headers'];
            if (!empty($map['cookies'])) $params['cookies'] = CookieJar::fromArray($map['cookies'], $url);

            $httpType = 0;
            if (isset($map['sync'])) $httpType = $map['sync'];

            $sendData = [
                'GoHTTP' => '发送数据',
                'URL' => $url,
                'Method' => $method,
                'ContentType' => $contentType,
                'Data' => $params
            ];
            Go::Log($sendData, 'do_log');
            return self::doHttp($httpType, $method, $url, $params)->current();
        } else return '未知数据格式';
    }

    /**
     * 发送 http 请求数据 支持 GET POST
     * @param string $url
     * @param array|null $params
     * @param array|null $headers
     * @param array|null $cookies
     * @param string $contentType
     * @param int $httpType
     * @param string $method
     * @return array|mixed|string
     * @author Eggo
     * @date 2022-02-23 19:09
     */

    public static function send(string $url,
                                array  $params = null,
                                array  $headers = null,
                                array  $cookies = null,
                                string $contentType = 'form_params',
                                string $method = 'POST',
                                int    $httpType = 0)
    {

        if (empty($contentType)) $contentType = 'form_params';
        $contentType = strtolower($contentType);
        $params = strtoupper($method) == 'GET' ? ['query' => $params] : [$contentType => $params];
        if (!empty($headers)) $params['headers'] = $headers;
        if (!empty($cookies)) $params['cookies'] = CookieJar::fromArray($cookies, $url);
        $sendData = [
            'GoHTTP' => '发送数据',
            'URL' => $url,
            'Method' => $method,
            'ContentType' => $contentType,
            'Data' => $params
        ];
        Go::Log($sendData, 'http_log');
        return self::doHttp($httpType, $method, $url, $params)->current();
    }

    /**
     *
     * @param $httpType
     * @param $method
     * @param $url
     * @param $params
     * @return Generator|string
     * @author Eggo
     * @date 2022-02-24 22:43
     */
    protected static function doHttp($httpType, $method, $url, $params)
    {
        if (!isset($httpType) && !isset($url)) exit();
        switch ($httpType) {
            case 0:  // 异步发送回调返回值
                // echo '发送是异步0';
                $promise = self::AsyncRequest($method, $url, $params)->current();
                yield $promise->then(function (ResponseInterface $res) {
                    return self::getParams($res)->current();
                }, function (RequestException $e) {
                    return self::getError($e)->current();
                })->wait();
                break;
            case 1: // 同步发送返回值
                try {
                    // echo '发送是同步1';
                    $res = self::SyncRequest($method, $url, $params)->current();
                    yield self::getParams($res)->current();
                } catch (RequestException $e) {
                    yield self::getError($e)->current();
                } catch (GuzzleException $e) {
                    Go::Log('GuzzleHttp error', (string)$e);
                }
                break;
        }
        return '未知HTTP类型';
    }

    /**
     *
     * 异步发送数据
     * @param $method
     * @param $url
     * @param $params
     * @return Generator
     * @author Eggo
     * @date 2022-02-23 21:38
     */
    protected static function AsyncRequest($method, $url, $params): Generator
    {
        yield self::$httpClient->requestAsync($method, $url, $params);
    }

    /**
     *
     * 同步发送数据
     * @param $method
     * @param $url
     * @param $params
     * @return Generator
     * @throws GuzzleException
     * @author Eggo
     * @date 2022-02-23 21:38
     */
    protected static function SyncRequest($method, $url, $params): Generator
    {
        yield self::$httpClient->request($method, $url, $params);
    }

    /**
     *
     * 获取HTTP错误
     * @param $res
     * @return Generator
     * @author Eggo
     * @date 2022-02-23 20:48
     */
    protected static function getError($res): Generator
    {
        $Error['errorCode'] = $res->getCode();
        $Error['errorMessage'] = $res->getMessage();
        $Error['httpMethod'] = $res->getRequest()->getMethod();
        $HttpError = array('HTTPError', $Error);
        Go::Log($HttpError, 'http_log');
        yield $Error;
    }

    /**
     *
     * 获取HTTP请求状态返回值
     * @param $res
     * @return Generator
     * @author Eggo
     * @date 2022-02-23 20:48
     */
    protected static function getParams($res): Generator
    {
        $Param['httpCode'] = $res->getStatusCode();
        $Param['Response'] = self::ResponseToArray($res->getBody()->getContents())->current();
        $Param['HeaderLine'] = $res->getHeaderLine('content-type');
        $Param['ReasonPhrase'] = $res->getReasonPhrase();
        $httpStatus = $Param['httpCode'] == 200 ? 'success' : 'failed';
        $HttpInfo = array(sprintf('HTTP返回数据: %s', $httpStatus), $Param);
        Go::Log($HttpInfo, 'http_log');
        if ($Param['httpCode'] != 200) yield ['Code' => $Param['httpCode'], 'ErrorMessage' => 'Error'];
        yield $Param['Response'];
    }

    /**
     *
     * 解析 http 返回值
     * @param $data
     * @return Generator
     * @author Eggo
     * @date 2022-02-23 23:45
     */
    protected static function ResponseToArray($data): Generator
    {
        switch ($data) {
            case !is_null(@json_decode($data)):
                yield json_decode($data, true);
                break;
            case is_array($data):
            case is_string($data):
                yield $data;
                break;
            case is_object($data):
                yield get_object_vars($data);
                break;
            default:
                yield 'NoData';
                break;
        }
    }


}