<?php

namespace Eggo\lib;

use Exception;

/**
 * Class GoCurl
 * @package lib
 */
class GoCurl
{
    protected $headers;
    protected $connectTimeout;
    protected $socketTimeout;
    protected $conf;

    /**
     * HttpClient
     * @param array $headers HTTP header
     */
    public function __construct(array $headers = array())
    {
        $this->headers = $this->buildHeaders($headers);
        $this->connectTimeout = 60000;
        $this->socketTimeout = 60000;
        $this->conf = array();
    }

    /**
     * 连接超时
     * @param int $ms 毫秒
     */
    public function setConnectionTimeoutInMillis(int $ms)
    {
        $this->connectTimeout = $ms;
    }

    /**
     * 响应超时
     * @param int $ms 毫秒
     */
    public function setSocketTimeoutInMillis(int $ms)
    {
        $this->socketTimeout = $ms;
    }

    /**
     * 配置
     * @param array $conf
     */
    public function setConf(array $conf)
    {
        $this->conf = $conf;
    }

    /**
     * 请求预处理
     * @param resource $ch
     */
    public function prepare($ch)
    {
        foreach ($this->conf as $key => $value) {
            curl_setopt($ch, $key, $value);
        }
    }

    /**
     * @param string $url
     * @param array $data HTTP POST BODY
     * @param array $params HTTP URL
     * @param array $headers HTTP header
     * @return array
     * @throws Exception
     */
    public function curlPost(string $url, array $data = array(), array $params = array(), array $headers = array()): array
    {
        $url = $this->buildUrl($url, $params);
        $headers = array_merge($this->headers, $this->buildHeaders($headers));

        $ch = curl_init();
        $this->prepare($ch);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? http_build_query($data) : $data);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->socketTimeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->connectTimeout);
        $content = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($code === 0) {
            throw new Exception(curl_error($ch));
        }

        curl_close($ch);
        return array(
            'code' => $code,
            'content' => $content,
        );
    }


    /**
     * @param string $url
     * @param array $data_s HTTP POST BODY
     * @param array $params HTTP URL
     * @param array $headers HTTP header
     * @return array
     * @throws Exception
     */
    public function multiPost(string $url, array $data_s = array(), array $params = array(), array $headers = array()): array
    {
        $url = $this->buildUrl($url, $params);
        $headers = array_merge($this->headers, $this->buildHeaders($headers));

        $chs = array();
        $result = array();
        $mh = curl_multi_init();
        foreach ($data_s as $data) {
            $ch = curl_init();
            $chs[] = $ch;
            $this->prepare($ch);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? http_build_query($data) : $data);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->socketTimeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->connectTimeout);
            curl_multi_add_handle($mh, $ch);
        }

        $running = null;
        do {
            curl_multi_exec($mh, $running);
            usleep(100);
        } while ($running);

        foreach ($chs as $ch) {
            $content = curl_multi_getcontent($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $result[] = array(
                'code' => $code,
                'content' => $content,
            );
            curl_multi_remove_handle($mh, $ch);
        }
        curl_multi_close($mh);

        return $result;
    }

    /**
     * @param string $url
     * @param array $params HTTP URL
     * @param array $headers HTTP header
     * @return array
     * @throws Exception
     */
    public function CurlGet(string $url, array $params = [], array $headers = []): array
    {
        $url = $this->buildUrl($url, $params);
        $headers = array_merge($this->headers, $this->buildHeaders($headers));

        $ch = curl_init();
        $this->prepare($ch);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->socketTimeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->connectTimeout);
        $content = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($code === 0) {
            throw new Exception(curl_error($ch));
        }

        curl_close($ch);
        return array(
            'code' => $code,
            'content' => $content,
        );
    }

    /**
     * 构造 header
     * @param array $headers
     * @return array
     */
    private function buildHeaders(array $headers): array
    {
        $result = array();
        foreach ($headers as $k => $v) {
            $result[] = sprintf('%s:%s', $k, $v);
        }
        return $result;
    }

    /**
     *
     * @param string $url
     * @param array $params 参数
     * @return string
     */
    private function buildUrl(string $url, array $params): string
    {
        if (!empty($params)) {
            $str = http_build_query($params);
            return $url . (strpos($url, '?') === false ? '?' : '&') . $str;
        } else {
            return $url;
        }
    }

}
