<?php
// 自动加载文件
const BASE_PATH = __DIR__ . DIRECTORY_SEPARATOR;
/**
 *
 * (c) PHP RouterBase Team <670687@qq.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if ($uri !== '/' && file_exists(BASE_PATH . 'public' . $uri)) {
    return false;
}
$_GET['_url'] = $_SERVER['REQUEST_URI'];
require_once BASE_PATH . 'public/index.php';