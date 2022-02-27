<?php
declare(strict_types=1);

namespace Eggo\lib;

use Generator;
use stdClass;


class GoFunc
{
    private static $_instance = [];

    final private function __construct()
    {
    }

    final private function __clone()
    {
    }

    final public static function init()
    {
        return static::$_instance[static::class] ?? static::$_instance[static::class] = new static();
    }

    /**
     * ajax
     * @param int $code
     * @param string $message
     * @param null $data
     * @param string $type
     * @return string
     */
    public static function output(int $code, string $message = '', $data = null, string $type = 'json'): string
    {
        if (!is_numeric($code)) return '';
        switch (strtolower($type)) {
            case 'json':
                exit(self::json($code, $message, $data)->current());
                break;
            case 'array':
                header('Content-Type:text/html; charset=utf-8');
                exit(var_export(['code' => $code, 'message' => $message, 'data' => $data]));
                break;
            default:
                break;
        }
        return $type;
    }


    /**
     *
     * @param $code
     * @param string $message
     * @param null $data
     * @return Generator
     * @author Eggo
     * @date 2022-02-24 2:28
     */
    protected static function json($code, string $message = '', $data = null): Generator
    {
        if (!is_numeric($code)) return '';
        $result = array(
            'code' => $code,
            'message' => $message,
            'data' => $data
        );
        header('Content-Type:application/json; charset=utf-8');
        yield json_encode($result, 448);
    }

    /**
     *
     * 格式化 输出 var_dump
     * @param $data
     * @author Eggo
     * @date 2022-02-24 1:58
     */
    public static function dump($data)
    {
        exit(self::out_dump($data)->current());
    }

    /**
     * @param $data
     * @return Generator|null
     */
    protected static function out_dump($data): ?Generator
    {
        ob_start();
        var_dump($data);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
            $output = '<pre>' . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
        yield $output;
    }


    /**
     * @param $data
     * @param string $filename
     * @param int $type 0 array_format 1 json_format
     */
    public static function Log($data, string $filename = '', int $type = 0)
    {
        var_export($_SERVER['DOCUMENT_ROOT']);
        if (!is_numeric($type)) return;
        if (!empty($filename)) $filename = sprintf('.%s', $filename);
        $date = date('Y-m-d');
        $file_dir = sprintf('%s/runtime/logs/%s/', @$_SERVER['DOCUMENT_ROOT'], $date);
        if (!is_dir($file_dir)) {
            @mkdir($file_dir, 0777, true);
        }
        $filepath = sprintf('%s%s%s.log', $file_dir, $date, $filename);
        self::out_file_put($filepath, $data, $type)->current();
    }

    protected static function out_file_put($filepath, $data, int $type = 0): Generator
    {
        if ($type == 1) $data = json_encode($data, 448);
        $datetime = date('Y-m-d H:i:s');
        $fileTxt = sprintf("%s\t%s%s", $datetime, var_export($data, true), PHP_EOL);
        yield file_put_contents($filepath, $fileTxt, FILE_APPEND);
    }


    /**
     * @param $filename
     */
    public static function getLog($filename)
    {
        $date = date('Ymd');
        $log_path = sprintf('%s/runtime/logs/%s/%s', @$_SERVER['DOCUMENT_ROOT'], $date, $filename);
        if (file_exists($log_path)) {
            $out_Log = file($log_path);
            foreach ($out_Log as $v) echo $v . '<br/>';
            exit(0);
        } else GoFunc::go(-1, '日志文件不存或未生成!');
    }

    /**
     * 获取或保存文件内容
     * @param string $fileDir
     * @param string $content 文件内容
     * @return string
     */
    function FileContent(string $fileDir = '', string $content = '')
    {
        if (empty($fileDir)) return '';
        if (empty($content)) {
            if (file_exists($fileDir)) {
                $fp = fopen($fileDir, 'r');
                $content = file_get_contents($fileDir);
                fclose($fp);
                return $content;
            } else return '';
        } else {
            $fps = fopen($fileDir, 'a');
            file_put_contents($fileDir, $content);
            fclose($fps);
            return true;
        }
    }

    /**
     * @param $data
     * @return bool
     */
    public static function is_json($data): bool
    {
        if (is_string($data)) {
            @json_decode($data);
            return (json_last_error() === JSON_ERROR_NONE);
        }
        return false;
    }

    /**
     * get_ip
     * @return array|false|mixed|string
     */
    public static function get_ip1()
    {
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown'))
            $ip = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown'))
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown'))
            $ip = getenv('REMOTE_ADDR');
        else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown'))
            $ip = $_SERVER['REMOTE_ADDR'];
        else
            $ip = 'unknown';
        return ($ip);
    }

    /**
     * 获取客户端IP地址
     * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    public static function get_ip(int $type = 0, bool $adv = false)
    {
        $type = $type ? 1 : 0;         //判断是否传入type=1参数，又则求ipv4地址数字
        static $ip = null;             //定义为静态变量，防止重复调用
        if (null !== $ip) {
            return $ip[$type];
        }
        if ($adv) {
            //对用户是否传参数判断通过什么方式获取ip
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);    //等到代理ip和用户本机ip
                $pos = array_search('unknown', $arr);                       //对获取的值进行过滤判断
                if (false !== $pos) {
                    unset($arr[$pos]);
                }
                $ip = trim(current($arr));
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf('%u', ip2long((string)$ip));
        $ip = $long ? [$ip, $long] : ['0.0.0.0', 0];
        return $ip[$type];
    }

    /**
     * isMobile
     * @return bool
     */
    public static function isMobile(): bool
    {
        if (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], 'wap')) {
            return true;
        } elseif (isset($_SERVER['HTTP_ACCEPT']) && strpos(strtoupper($_SERVER['HTTP_ACCEPT']), 'VND.WAP.WML')) {
            return true;
        } elseif (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
            return true;
        } elseif (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $_SERVER['HTTP_USER_AGENT'])) {
            return true;
        } else {
            return false;
        }
    }


    //multi_pos

    /**
     *
     * @param string $string
     * @param array $ext
     * @param bool $getResults
     * @return array|false|int
     * @author Eggo
     * @date 2022-02-26 15:44
     */
    public static function pos(string $string, array $ext, bool $getResults = false)
    {
        if (empty($ext)) return false;
        $result = array();
        foreach ($ext as $s) {
            $pos = stripos($string, $s);
            if ($pos !== false) if ($getResults) $result[$s] = $pos; else return $pos;
        }
        return empty($result) ? false : $result;
    }

    /**
     * 获取用户设备信息
     */

    public static function equipmentSystem(): string
    {
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (preg_match('/Android (([0-9_.]{1,3})+)/i', $agent, $version)) {
            $fb_fs = sprintf('(Android %s)', $version[1]);
        } else if (preg_match('/iPhone OS (([0-9_.]{1,3})+)/i', $agent, $version)) {
            $fb_fs = sprintf('(iPhone %s)', $version[1]);
        } else if (preg_match('/Mac OS X (([0-9_.]{1,5})+)/i', $agent, $version)) {
            $fb_fs = sprintf('(OS X %s)', $version[1]);
        } else if (stristr($agent, 'iPad')) {
            $fb_fs = '(iPad)';
        } else if (stristr($agent, 'Linux')) {
            $fb_fs = '(Linux)';
        } else if (preg_match('/unix/i', $agent)) {
            $fb_fs = 'Unix';
        } else if (preg_match('/windows/i', $agent)) {
            $fb_fs = '(Windows)';
        } else {
            $fb_fs = 'Unknown';
        }
        return $fb_fs;
    }

    /**
     * @return string
     */
    public static function get_mobile(): string
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $getOS = self::equipmentSystem();
        $brand = '';
        if (self::pos($user_agent, ['iPhone', 'iPad']) !== false) $brand = '苹果';
        else if (self::pos($user_agent, ['SAMSUNG', 'SM', 'Galaxy', 'GT-', 'SCH-']) !== false) $brand = '三星';
        else if (self::pos($user_agent, ['HUAWEI', 'JKM-AL00b', 'MRD-AL00']) !== false) $brand = '华为';
        else if (self::pos($user_agent, ['MI', 'Redmi', 'MIX', 'HM', 'SKW-A0']) !== false) $brand = '小米';
        else if (self::pos($user_agent, ['Coolpad', '8190Q', '5910']) !== false) $brand = '酷派';
        else if (self::pos($user_agent, ['ZTE', 'X9180', 'N9180', 'U9180']) !== false) $brand = '中兴';
        else if (self::pos($user_agent, ['OPPO', 'O11019', 'PBDT00', 'X9007', 'X907', 'X909', 'R831S', 'R827T', 'R821T', 'R811', 'R2017']) !== false) $brand = 'OPPO';
        else if (self::pos($user_agent, ['HTC', 'Desire']) !== false) $brand = 'HTC';
        else if (self::pos($user_agent, ['Gionee', 'GN']) !== false) $brand = '金立';
        else if (self::pos($user_agent, ['HS-U', 'HS-E']) !== false) $brand = '海信';
        else if (self::pos($user_agent, ['Nubia', 'NX50', 'NX40']) !== false) $brand = '努比亚';
        else if (self::pos($user_agent, ['PRO 6', 'PRO 5', 'M5', 'm2', 'MX', 'MRA', 'M355']) !== false) $brand = '魅族';
        else if (self::pos($user_agent, ['DOOV']) !== false) $brand = '朵唯';
        else if (self::pos($user_agent, ['GFIVE']) !== false) $brand = '基伍';
        else if (self::pos($user_agent, ['vivo', 'V19', 'V18']) !== false) $brand = 'ViVo';
        else if (self::pos($user_agent, ['K-Touch']) !== false) $brand = '天语';
        else if (self::pos($user_agent, ['Nokia']) !== false) $brand = '诺基亚';
        else if (self::pos($user_agent, ['Lenovo']) !== false) $brand = '联想';
        else if (self::pos($user_agent, ['ONEPLUS']) !== false) $brand = '一加';
        else if (self::pos($user_agent, ['OD105', 'SM8', 'SM9']) !== false) $brand = '锤子';
        else if (self::pos($user_agent, ['RMX18', 'RMX19']) !== false) $brand = 'Realme';
        else if (self::pos($user_agent, ['Windows']) !== false) $brand = 'Windows';
        else self::Log($user_agent, 'Mobile_agent');
        return $brand . $getOS;
    }


    /**
     * MyUrl
     * @return string
     */
    public static function MyUrl(): string
    {
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'exception://';
        return $http_type . $_SERVER['HTTP_HOST'];
    }

    /**
     * @param $msg
     */
    public static function ShowMsg($msg)
    {
        echo sprintf("<script type='text/javascript'>alert('%s');history.go(-1);</script>", $msg);
        exit(0);
    }

    /**
     * @param $msg
     */
    public static function ShowAjax($msg)
    {
        echo sprintf('<script type="text/javascript">alert("%s");history.go(-1);</script>', $msg);
    }

    /**
     * @param $url
     */
    public static function ajax_url($url)
    {
        echo "<script type='text/javascript'>location.replace('" . $url . "'); </script>";
        exit(0);
    }

    public static function checkAdminSession()
    {
        if (!isset($_SESSION['SystemUserName'])) {
            header('location: /admin/system');
            exit();
        }
        // return $_SESSION['SystemUserName'];
    }

    /**
     * @param string $id
     * @param string $SessionName
     * @return bool|mixed|string
     */
    public static function CheckSession(string $id, string $SessionName)
    {
        if (empty($SessionName)) return false;
        switch ($id) {
            case 0:
                if (!isset($_SESSION[$SessionName])) header('location: /admin/system');
                return $_SESSION[$SessionName];
            case 1:
                if (!isset($_SESSION[$SessionName])) header('location: /shop/login');
                return $_SESSION[$SessionName];
            case 2:
                if (!isset($_SESSION[$SessionName])) header('location: /agent/login');
                return $_SESSION[$SessionName];
            case 3:
                if (!isset($_SESSION[$SessionName])) header('location: /coder/login');
                return $_SESSION[$SessionName];
            case 4:
                unset($_SESSION[$SessionName]);
                break;
            default:
                break;
        }
        return '';
    }

    /**
     * @param $m
     * @param $n
     * @param $symbol
     * @return mixed|string|null
     */
    public static function calc($m, $n, $symbol): ?string
    {
        $errors = array('被除数不能为零', '负数没有平方根');
        $scale = '2';
        $res = '';
        switch ($symbol) {
            case 'add':     //加法
                $res = bcadd($m, $n, $scale);
                break;
            case 'sub':     //减法
                $res = bcsub($m, $n, $scale);
                break;
            case 'mul':     //乘法'*'
                $res = bcmul($m, $n);
                break;
            case 'div':     //除法'/'
                if ($n == 0) return $errors[0];
                $res = bcdiv($m, $n);
                break;
            case 'mod':     //求余取模%
                if ($n == 0) return $errors[0];
                $res = bcmod($m, $n);
                break;
            case 'pow':
                $res = bcpow($m, $n);
                break;
            case 'sqrt':
                if ($m <= 0) return $errors[1];
                $res = bcsqrt($m);
                break;
            default:
                break;
        }
        return $res;
    }

    /**
     * 价格由元转分
     * @param $price
     * @return int
     */
    public static function price_conversion($price)
    {
        return self::calc(100, self::ncPriceFormat($price), 'mul');
    }

    /**
     * 价格格式化
     *
     * @param int $price
     * @return string    $price_format
     */
    public static function ncPriceFormat(int $price): string
    {
        return number_format($price, 2, '.', '');
    }

    /**
     * @param $total
     * @param $fee
     * @param bool $flag
     * @return string
     */
    public static function setRate($total, $fee, bool $flag = false): string
    {
        $Rate = bcsub($total, $total * $fee / 100, 2);
        return $flag ? $Rate : bcsub($total, $Rate, 2);
        //return number_format($total - $total * $fee / 100, 2, '.', ',');
    }

    /**
     * 公式计算
     * @return string
     */
    public static function bc(): string
    {
        bcscale(3);
        $result = '';
        $argv = func_get_args();
        $string = str_replace(' ', '', '(' . $argv[0] . ')');
        $string = preg_replace_callback(/** @lang text */ '/\$([0-9\.]+)/', function () {
            return '$argv[$1]';
        }, $string);
        while (preg_match(/** @lang text */ '/(()?)\(([^\)\(]*)\)/', $string, $match)) {
            while (preg_match(/** @lang text */ '/([0-9\.]+)(\^)([0-9\.]+)/', $match[3], $m)
                || preg_match(/** @lang text */ '/([0-9\.]+)([\*\/\%])([0-9\.]+)/', $match[3], $m)
                || preg_match(/** @lang text */ '/([0-9\.]+)([\+\-])([0-9\.]+)/', $match[3], $m)) {
                switch ($m[2]) {
                    case '+':
                        $result = bcadd($m[1], $m[3]);
                        break;
                    case '-':
                        $result = bcsub($m[1], $m[3]);
                        break;
                    case '*':
                        $result = bcmul($m[1], $m[3]);
                        break;
                    case '/':
                        $result = bcdiv($m[1], $m[3]);
                        break;
                    case '%':
                        $result = bcmod($m[1], $m[3]);
                        break;
                    case '^':
                        $result = bcpow($m[1], $m[3]);
                        break;
                }
                $match[3] = str_replace($m[0], $result, $match[3]);
            }
            if (!empty($match[1]) && function_exists($func = 'bc' . $match[1])) $match[3] = $func($match[3]);
            $string = str_replace($match[0], $match[3], $string);
        }
        return sprintf('%.2f', $string);
    }

    /**
     * 检测变量是否为浮点true/false
     * @param $value
     * @return bool
     */
    public static function is_float($value): bool
    {
        return is_float($value + 0);
    }

    /**
     * @param $Array
     * @param $Keys
     * @param bool $Flag
     * @return array
     */
    public static function is_keys($Array, $Keys, bool $Flag = false): array
    {
        if (empty($Array)) return $Array;
        $Array = self::isToArray($Array);
        $ArrKeys = [];
        $keys = is_array($Keys) ? $Keys : array($Keys);
        if (!$Flag) {
            foreach ($keys as $k) if (isset($Array[$k])) $ArrKeys[$k] = $Array[$k];
            return $ArrKeys;
        } else {
            foreach ($keys as $k) unset($Array[$k]);
            return $Array;
        }
    }

    /**
     * 建立跳转请求表单
     * @param string $url 数据提交跳转到的URL
     * @param null $data 请求参数数组
     * @param string $method 提交方式：post或get 默认post
     * @return string 提交表单的HTML文本
     */
    public static function buildRequestForm(string $url, $data = null, string $method = 'post'): string
    {
        $sHtml = '<form id="requestForm" name="requestForm" action="' . $url . '" method="' . $method . '">';
        if (!empty($data)) foreach ($data as $k => $v) $sHtml .= sprintf('<input type="hidden" name="%s" value="%s" />', $k, $v);
        $sHtml = $sHtml . '<input type="submit" value="确定" style="display:none;"></form>';
        $sHtml = $sHtml . '<script type="text/javascript">document.forms["requestForm"].submit();</script>';
        self::Log($sHtml, 'sHtml', 1);
        return $sHtml;
    }

    /**
     * @param $url
     * @param $data
     */
    public static function doRedirect($url, $data)
    {
        $url = sprintf('%s?%s', $url, http_build_query($data));
        echo sprintf('<script>location.href="%s"</script>', $url);
    }

    /**
     * @param $url
     * @param $params
     * @return string
     */
    public static function buildUrl($url, $params): string
    {
        if (!empty($params)) {
            $str = http_build_query($params);
            //$str = http_build_query($params, null, '&', PHP_QUERY_RFC3986);
            return $url . (strpos($url, '?') === false ? '?' : '&') . $str;
        } else return $url;
    }

    /**
     * @param $m
     * @param $n
     * @param bool $add 默认自减
     * @param bool $shuffle 默认不随机
     * @param bool $reverse 默认不倒序
     * @return array
     */
    public static function BcNum($m, $n, bool $add = false, bool $shuffle = false, bool $reverse = false): array
    {
        if ($m < 1 || $m < $n) exit(0);
        $s = $add ? bcadd($m, $n, 2) : bcsub($m, $n, 2);
        $num = array_map(function ($x) {
            return sprintf('%.2f', $x);
        }, range($s, $m, .01));
        unset($num[0]);
        if ($shuffle) shuffle($num);
        return $reverse ? $num : array_reverse($num);
    }


    /**
     * @param $m
     * @param $n
     * @param bool $add
     * @param bool $shuffle
     * @param bool $reverse
     * @return array
     */
    public static function BcCN($m, $n, bool $add = false, bool $shuffle = false, bool $reverse = false): array
    {
        if ($m < 1 || $m < $n) exit(0);
        $s = $add ? bcadd($m, $n, 2) : bcsub($m, $n, 2);
        $num = array_map(function ($x) {
            return sprintf('%.2f', $x);
        }, range($s, $m, 1));
        unset($num[0]);
        if ($shuffle) shuffle($num);
        return $reverse ? $num : array_reverse($num);
    }

    /**
     * 过滤数组元素前后空格 (支持多维数组)
     * @param $array
     * @return array|string
     */
    public static function trim_array($array)
    {
        if (!is_array($array))
            return trim($array);
        return array_map(array('self', __FUNCTION__), $array);
    }

    /**
     * 数组 转 对象
     * @param $array
     * @return bool|stdClass
     */

    public static function arrayToObj($array)
    {
        if (!is_array($array)) return $array;
        $object = new stdClass();
        if (count($array) > 0) {
            foreach ($array as $name => $value) {
                $name = trim($name);
                if (!empty($name)) {
                    $object->$name = self::arrayToObj($value);
                }
            }
            return $object;
        } else return FALSE;
    }

    /**
     * @param $array
     * @param bool $flag
     * @return mixed
     */
    public static function reduceArray($array, bool $flag = false)
    {
        $array = self::isToArray($array);
        if ($flag) {
            return array_reduce($array, 'array_merge', array());
        } else {
            $res = array_reduce($array, function (&$res, $v) {
                return array_merge($res, (array)$v);
            }, array());
            return array_reduce($res, 'array_merge', array());
        }
    }


    /**
     * @param $data
     * @return array|mixed
     */
    public static function getToArray($data): array
    {
        if (is_object($data)) return get_object_vars($data);
        if (is_array($data)) return $data;
        return ($result = @json_decode($data, true)) ? $result : $data;
    }

    /**
     * @param $data
     * @return array
     */
    public static function isToArray($data): array
    {
        if (is_object($data)) $data = get_object_vars($data);
        return is_array($data) ? array_map(array('self', __FUNCTION__), $data) : $data;
    }

    /**
     * @param $object
     * @return mixed
     */
    public static function objToArr(&$object)
    {
        $object = json_decode(json_encode($object, 320), true);
        return $object;
    }

    /**
     * @param $array
     * @return array
     */
    public static function obj2array($array): array
    {
        if (is_object($array)) $array = (array)$array;
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = self::obj2array($value);
            }
        }
        return $array;
    }

    /**
     * @param $url
     * @param $data
     * @param string $method
     * @param string $HTTP
     * @return false|string
     */
    public static function contextPost($url, $data, string $method = 'POST', string $HTTP = 'exception')
    {
        if (!is_array($data)) exit(0);
        $options = [
            strtolower($HTTP) => array(
                'method' => strtoupper($method),
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($data),
                'timeout' => 10
            )
        ];
        $context = stream_context_create($options);
        return file_get_contents($url, false, $context);
    }

    /**
     * 格式化小数点两位
     * @param $param
     * @return array|string
     */
    public static function get_format($param)
    {
        if (is_object($param)) $param = get_object_vars($param);
        if (is_array($param)) return array_map(function ($f) {
            return sprintf('%.2f', $f);
        }, $param);
        return sprintf('%.2f', $param);
    }


    /**
     * @param $hex_ip
     * @return string
     */
    public static function hex_toIp($hex_ip): string
    {
        $ip = long2ip($hex_ip);
        $ip = explode('.', $ip);
        return sprintf('%s.%s.%s.%s', $ip[3], $ip[2], $ip[1], $ip[0]);
    }

    /**
     * @param $netmask
     * @return int
     */
    public static function mask2cidr($netmask): int
    {
        $cidr = 0;
        $netmask = self::hex_toIp($netmask);
        foreach (explode('.', $netmask) as $oct) {
            for (; $oct > 0; $oct = ($oct << 1) % 256) {
                $cidr++;
            }
        }
        return $cidr;
    }

    /**
     * @param $ip
     * @return bool
     */
    public static function is_ip($ip): bool
    {
        return long2ip(ip2long($ip)) == $ip;
    }

    /**
     * @param $size
     * @return string
     */
    public static function by2m($size): string
    {
        $sizeName = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
        return $size ? sprintf('%s %s', round($size / pow(1024, ($i = floor(log($size == 0 ? 1 : $size, 1024)))), 2), $sizeName[$i]) : '0 B';
    }

    /**
     * @param $size
     * @return string
     */
    public static function by2ms($size): string
    {
        $sizeName = ['b/s', 'Kib/s', 'Mib/s', 'Gib/s', 'Tib/s', 'Pib/s', 'Eib/s', 'Zib/s', 'Yib/s'];
        return $size * 8 ? sprintf('%s %s', round($size * 8 / pow(1024, ($i = floor(log($size == 0 ? 8 : $size * 8, 1024)))), 2), $sizeName[$i]) : '0 b/s';
    }

    /**
     * @param $arg
     * @return mixed
     */
    public static function version($arg)
    {
        preg_match('@[0-9].*@', $arg, $arg);
        return $arg[0];
    }


    /**
     * @param $arg
     * @return Generator
     */
    public static function cmd_yield($arg): Generator
    {
        $cmd = popen($arg, 'r');
        echo '<pre>';
        while ($result = fgets($cmd, 4096)) {
            yield $result;
            ob_flush();
        }
        pclose($cmd);
        echo '<pre>';
    }

    public static function command($arg)
    {
        $cmd_array = self::cmd_yield($arg);
        foreach ($cmd_array as $result) {
            echo $result . PHP_EOL;
        }
    }

    /**
     * @param $fileName
     * @return Generator
     */
    public static function read_file($fileName): Generator
    {
        $handle = fopen($fileName, 'r');
        while (!feof($handle)) {
            yield fgets($handle);
        }
        fclose($handle);
    }

    /**
     * $cfg = $this->loadConfig("/etc/cfg/cfg.cfg");
     * @param $file
     * @return array|false|int
     */
    public static function loadConfig($file)
    {
        if (file_exists($file)) {
            return parse_ini_file($file, true);
        }
        return 0;
    }

    /**
     * $cfg = $this->loadConfig("/etc/cfg/cfg.cfg");
     * $cfg["test"]='fff';
     * $cfg['test1']=99;
     * $this->writeConfig("/etc/cfg/cfg.cfg", $cfg);
     * @param $file
     * @param $content
     */
    public static function writeConfig($file, $content)
    {
        $conf = array();
        foreach ($content as $key => $value) {
            $conf[] = sprintf("$key='%s'", $value);
        }
        file_put_contents($file, implode("\n", $conf));
    }

    /**
     * $cnf = parse_ini_file("/etc/cfg/cfg.cnf", true);
     * $cnf["key"]["keys"] = "value";
     * $this->write_ini_file("/tmp/cfg.cnf",$cnf);
     * @param $array
     * @param $file
     */
    public static function write_ini_file($file, $array)
    {
        $res = array();
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $res[] = sprintf('[%s]', $key);
                foreach ($val as $sKey => $sVal) {
                    $res[] = sprintf('%s = %s', $sKey, $sVal);
                }
            } else $res[] = sprintf('%s = %s', $key, $val);
        }
        file_put_contents($file, implode("\n", $res));
    }

    /**
     * @param $file
     * @param $content
     */
    public static function write_file($file, $content)
    {
        file_put_contents($file, str_replace(array("\r\n", "\n\r"), "\n", $content));
    }


    public static function force_update_page()
    {
        header(sprintf('Location: %s', $_SERVER['REQUEST_URI']));
    }

    /**
     * @param $arg
     * @return string
     */
    public static function post($arg): string
    {
        return html_entity_decode($_POST[$arg]);
    }

    /**
     * @return string
     */
    public static function host_url(): string
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) {
            $web_host = "https://";
        } else $web_host = "exception://";
        return $web_host;
    }

    /**
     * @param $num
     * @return bool
     */
    public static function is_number($num): bool
    {
        if (!is_numeric($num) || strpos($num, '.') !== false) {
            return true;
        } else return false;
    }

    /**
     * isPhone
     * @param $mobile
     * @return bool
     */
    public static function isPhone($mobile): bool
    {
        if (preg_match('/^(13[0-9]|14[579]|15[0-3,5-9]|16[6]|17[0135678]|18[0-9]|19[89])\d{8}$/', $mobile)) {
            return true;
        }
        return false;
    }


    /**
     * isEmail
     * @param $email
     * @return bool
     */
    public static function isEmail($email): bool
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        return false;
    }


}
