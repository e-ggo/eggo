<?php
declare(strict_types=1);

namespace Eggo\lib;

use Generator;

/**
 *
 * 本方法完全单例运行加载配置方法
 * GoConfig::Load 初始化加载
 * 调用方法加载文件或目录 $config = GoConfig::Load('config'); config可以是目录或单文件config.php
 * $config::get('name'); 获取全部name下的数组参数
 * $config::get('name.db'); 获取name名下的db参数
 * 或者 $db=$config::get('name')['db']; 参数多的无需一个个调用$db['host']
 * 判断是否存在某个配置
 * $config::has('template');  判断config目录下是否存在template这个配置参数
 * $config::has('name.db');
 * 更改设置参数
 * $config::set(['name1' => 'value1', 'name2' => 'value2'], 'name');
 *
 * @mail eggo.com.cn@gmail.com
 * @author Eggo <https://www.eggo.com.cn>
 * @date 2022-02-25 19:26
 */
class GoConfig
{
    private static $instance = [];
    /**
     * 配置参数
     * @var array
     */
    protected static $config = [];

    /**
     * 应用根目录
     * @var string
     */
    protected static $baseDir;

    /**
     * 配置文件目录
     * @var string
     */
    protected static $path;

    final private function __clone()
    {
    }

    /**
     * 构造方法
     * @access public
     */
    final private function __construct(string $path = null)
    {
        if (empty($path)) return;
        switch ($path) {
            case is_file($path):
            case is_dir($path):
                self::load($path);
                break;
            default:
                $path = ROOT . $path;
                echo '文件或目录不存在:' . $path;
                break;
        }
    }

    /**
     *
     * @param ...$args
     * @return mixed|static
     * @author Eggo
     * @date 2022-02-25 21:31
     */
    final public static function init(...$args)
    {
        return static::$instance[static::class] ?? static::$instance[static::class] = new static(...$args);
    }

    /**
     *
     * 获取配置参数 为空则获取所有配置
     * @access public
     * @param string|null $name 配置参数名（支持多级配置 .号分割）
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function get(string $name = null, $default = null)
    {
        if (empty($name)) return self::$config;    //为空时返回所有加载的参数
        if (false === strpos($name, '.')) return self::pull($name);
        $name = explode('.', $name);      // 拆分数组
        $name[0] = strtolower($name[0]);           // 别名名称
        $config = self::$config;
        // 按.拆分成多维数组进行判断
        foreach ($name as $val) {
            if (isset($config[$val])) {
                $config = $config[$val];
            } else return $default;
        }
        return $config;
    }

    /**
     *
     * 设置配置参数 name为数组则为批量设置
     * @param array $config 配置数组合集
     * @param string|null $name 配置名 别名
     * @return array
     * @author Eggo
     * @date 2022-02-25 17:20
     */
    public static function set(array $config, string $name = null): array
    {
        if (!empty($name)) {
            if (isset(self::$config[$name])) {
                $result = array_merge(self::$config[$name], $config);
            } else $result = $config;
            self::$config[$name] = $result;
        } else $result = self::$config = array_merge(self::$config, array_change_key_case($config));
        return $result;
    }

    /**
     * 检测配置是否存在
     * @access public
     * @param string $name 配置参数名（支持多级配置 .号分割）
     * @return bool
     */
    public static function has(string $name): bool
    {
        if (false === strpos($name, '.') && !isset(self::$config[strtolower($name)])) {
            return false;
        }
        return !is_null(self::get($name));
    }

    /**
     *
     * 支持单文件 和 目录下一次性配置文件快速高性能加载
     * 指定加载配置文件（格式.php|.ini|.json|.yml|.yaml）
     * 自动根据文件名 生成配置名 别名转换为小写
     * @param string $FilesPath
     * @return array
     * @author Eggo
     * @date 2022-02-25 17:14
     */
    protected function load(string $FilesPath): array
    {
        $FilesPath = self::GetFileName($FilesPath);
        foreach ($FilesPath as $file) {
            $name = pathinfo($file)['filename'];
            self::parse($file, strtolower($name));
        }
        return self::$config;
    }

    /**
     *
     * 如果单文件加载 是目录下全部加载 支持多级目录下文件加载
     * 指定的文件加载
     * @param $FilePath
     * @return Generator
     * @author Eggo
     * @date 2022-02-25 17:00
     */
    protected static function GetFileName($FilePath): Generator
    {
        if (is_file($FilePath)) yield $FilePath;
        if (is_dir($FilePath) === false) return;
        $Files = array_diff(scandir($FilePath), array('.', '..'));
        foreach ($Files as $File) {
            $fullPath = rtrim($FilePath, '/') . DIRECTORY_SEPARATOR . $File;
            if (is_dir($fullPath)) foreach (self::GetFileName($fullPath) as $Path) {
                yield $Path;
            } else {
                if (self::multiPos($File, ['.php', '.json', '.ini', '.yml', '.yaml']) === false) continue;
                yield $fullPath;
            }
        }
    }

    /**
     *
     * 指定加载文件格式
     * @param string $string
     * @param array $ext
     * @param bool $getResults
     * @return array|false|int
     * @author Eggo
     * @date 2022-02-25 17:00
     */
    protected static function multiPos(
        string $string,
        array  $ext,
        bool   $getResults = false
    )
    {
        $stat = [];
        $check = $ext;
        foreach ($check as $s) {
            $pos = stripos($string, $s);
            if ($pos !== false) {
                if ($getResults) {
                    $stat[$s] = $pos;
                } else return $pos;
            }
        }
        return empty($stat) ? false : $stat;
    }

    /**
     * 解析配置文件
     * @access public
     * @param string $file 配置文件名
     * @param string $name 一级配置名 别名
     * @return array
     */
    protected static function parse(string $file, string $name): array
    {
        $type = pathinfo($file, PATHINFO_EXTENSION);
        $config = [];
        switch ($type) {
            case 'php':
                // $config = include $file;
                $config = require_once $file;
                break;
            case 'yml':
            case 'yaml':
                if (!function_exists('yaml_parse_file')) return [];
                $config = yaml_parse_file($file);
                break;
            case 'ini':
                $config = parse_ini_file($file, true, INI_SCANNER_TYPED) ?: [];
                break;
            case 'json':
                $config = json_decode(file_get_contents($file), true);
                break;
        }
        return is_array($config) ? self::set($config, strtolower($name)) : [];
    }

    /**
     * 获取一级配置
     * @access protected
     * @param string $name 一级配置名
     * @return array
     */
    protected static function pull(string $name): array
    {
        $name = strtolower($name);
        return self::$config[$name] ?? [];
    }


}