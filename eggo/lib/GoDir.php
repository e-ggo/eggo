<?php
declare(strict_types=1);

namespace Eggo\lib;

use Generator;
use InvalidArgumentException;

class GoDir
{
    /**
     * 定位文件名
     * @param $dir
     * @param $filename
     * @return Generator
     */
    public static function FindName($dir, $filename): Generator
    {
        foreach (self::GetToFileArray($dir) as $data) {
            if ($data['filename'] === $filename) {
                yield $data;
            }
        }
    }

    /**
     * 获取所有 目录下.PHP文件 返回数组
     * @param $dir
     * @return Generator
     */
    public static function GetToFileArray($dir): Generator
    {
        if (is_dir($dir) === false) {
            throw new InvalidArgumentException('invalid dir');
        }
        $files = scandir($dir);
        $files = array_filter($files, function ($file) {
            $exceptions = ['.', '..'];
            return !in_array($file, $exceptions);
        });
        foreach ($files as $file) {
            $fullPath = rtrim($dir, '/') . '/' . $file;
            if (is_dir($fullPath)) {
                foreach (self::GetToFileArray($fullPath) as $res) {
                    yield $res;
                }
            } else {
                yield ['dir' => $dir . '/', 'filename' => $file];
            }
        }
    }

    /**
     * 获取 所有目录.php文件
     * @param $dir
     * @return Generator
     */
    public static function GetFileToName($dir): Generator
    {
        if (is_dir($dir) === false) {
            throw new InvalidArgumentException('invalid dir');
        }
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $fullPath = rtrim($dir, '/') . '/' . $file;
            if (is_dir($fullPath)) {
                foreach (self::GetFileToName($fullPath) as $res) {
                    yield $res;
                }
            } else {
                if (stripos($file, '.php') === false) continue;
                yield $dir . '/' . $file;
            }
        }
    }

    /**
     *  { 获取所有目录下的文件 }
     * @param $dir
     * @param bool $fullPath
     * @return Generator
     */
    public static function GetDirToFile($dir, bool $fullPath = false): Generator
    {
        if (is_dir($dir) === false) {
            throw new InvalidArgumentException('invalid dir');
        }
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $Path = $dir . DIRECTORY_SEPARATOR . $file; // 可以不带根路径默认关闭 返回控制器路径如 app\controllers
            if ($fullPath) $Path = realpath($Path);     //所有根目录下全文件路径
            if (!is_dir($Path)) {
                yield $Path;
            } else if ($file != '.' && $file != '..') {
                yield from self::GetDirToFile($Path);
                // yield $fullPath; 目录
            }
        }
    }

    /**
     * { 获取所有目录下的文件 $filter 获取的文件后辍 }
     * @param $dir
     * @param array $filter
     * @return Generator
     */
    public static function GetDirContents($dir, array $filter = []): Generator
    {
        foreach (self::GetDirToFile($dir) as $data) if (!empty($filter)) {
            if (self::multiPos($data, $filter) !== false) yield $data;
        } else {
            yield $data;
        }
    }


    /**
     *  { 获取所有目录下的文件 $filter 获取的文件后辍 '.'删除后辍名 }
     * @param $dir
     * @return Generator
     */
    public static function GetNamespace($dir, array $filter = []): Generator
    {
        foreach (self::GetDirToFile($dir) as $data) if (!empty($filter)) {
            if (self::multiPos($data, $filter) !== false) yield explode('.', $data)[0];
        } else {
            yield explode('.', $data)[0];
        }
        foreach (self::GetDirToFile($dir) as $data) if (!empty($filter)) {
            $classFile = realpath($data);
            // yield;
            yield $class = explode('.', $data)[0];
            // yield $data;
            if (!class_exists($class)) {
                require_once($classFile);
                yield;
                echo '没有装截';
            }
        }
    }

    /**
     * 字符串快速匹配 多条件数组
     * multi_pos
     * @param $string
     * @param $check
     * @param bool $getResults
     * @return array|false|int
     */
    public static function multiPos($string, $check, bool $getResults = false)
    {
        $result = array();
        $check = (array)$check;
        foreach ($check as $s) {
            $pos = stripos($string, $s);
            if ($pos !== false) {
                if ($getResults) {
                    $result[$s] = $pos;
                } else {
                    return $pos;
                }
            }
        }
        return empty($result) ? false : $result;
    }

    /**
     * @param string $path
     * @return array
     */
    public static function each(string $path): array
    {
        if (is_file($path)) return [include $path]; else {
            $dir = $path;
        }
        $dh = @opendir($dir);
        if (!$dh) {
            throw new \RuntimeException(sprintf('Invalid path: %s', $path));
        }
        $files = [];
        while (false !== ($file = readdir($dh))) {
            if (($file != '.') && ($file != '..')) {
                $full = $dir . '/' . $file;
                $info = pathinfo($file);
                $ext = $info['extension'] ?? '';
                if (is_file($full) && $ext == 'php') {
                    $files[] = $full;
                }
            }
        }
        closedir($dh);
        asort($files);
        $config = [];
        foreach ($files as $file) {
            array_push($config, include $file);
        }
        return $config;
    }


}