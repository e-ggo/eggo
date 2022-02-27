<?php


namespace Eggo\lib;


/**
 * @mail eggo.com.cn@gmail.com
 * @author Eggo <https://www.eggo.com.cn>
 * @date 2022-02-23 13:07
 */
class GoSign
{
    /**
     * @param $data
     * @param $key
     * @param bool $kLower
     * @param bool $flag
     * @return string
     */
    public static function MySign($data, $key, bool $kLower, bool $flag): string
    {
        $data = self::toArray($data);
        //签名步骤一按字典序排序数组参数
        $param = self::trimEmptyParam($data);
        ksort($param);
        $sign = self::to_url_params($param);
        //签名步骤二在 string 后加入 Key
        $sign = $kLower ? $sign . '&key=' . $key : $sign . '&Key=' . $key;
        Func::Log($sign, 'Sign');
        //签名步骤三md5 默认小写加密
        if ($flag) return strtoupper(md5($sign));
        return strtolower(md5($sign));
    }

    //将参数拼接为 url

    /**
     * @param $param
     * @return string
     */
    private static function to_url_params($param)
    {
        $string = '';
        if (!empty($param)) {
            $array = array();
            foreach ($param as $k => $v) {
                $array[] = $k . '=' . $v;
            }
            $string = implode('&', $array);
        }
        return $string;
    }


    private static function toArray($data)
    {
        if (is_object($data)) $data = get_object_vars($data);
        return is_array($data) ? array_map(array('self', __FUNCTION__), $data) : $data;
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
     * @param $data
     * @return array
     */
    private static function trimEmptyParam($data): array
    {
        $result = [];
        foreach ($data as $k => $v) {
            if (trim((string)$v) === '')
                continue;
            $result[$k] = $v;
        }
        return $result;
    }


    /**
     * @param $Array
     * @param $Keys
     * @param bool $Flag
     * @return array
     */
    private static function array_key($Array, $Keys, bool $Flag): array
    {
        if (empty($Array)) return $Array;
        $Array = self::toArray($Array);
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
     * @param $data
     * @param $key
     * @param bool $kLower
     * @param bool $flag
     * @return string
     */
    public static function md5Sign($data, $key, bool $kLower, bool $flag): string
    {
        $data = self::toArray($data);
        $data = static::trimEmptyParam($data);
        ksort($data);
        if ($kLower) {
            $sign = md5(urldecode(http_build_query($data)) . '&key=' . $key);
        } else {
            $sign = md5(urldecode(http_build_query($data)) . '&Key=' . $key);
            Func::Log(urldecode(http_build_query($data)) . '&Key=' . $key, 'Sign');
            Func::Log($sign, 'Sign');
        }
        // MyClass::debug($sign, 'Sign');
        // 签名md5 默认false使用大写Key小写加密
        if ($flag) return strtoupper($sign);
        return strtolower($sign);
    }

    /**
     * @param $data
     * @param $Key
     * @param array|null $param
     * @return array
     */
    public static function MakeMySign($data, $Key, array $param = null): array
    {
        $data = self::toArray($data);
        $SignData = self::array_key($data, ['OrderNo', 'OrderId', 'OrderMoney', 'PosUid']);
        $Sign = self::md5Sign($SignData, $Key);
        $CallBackData = self::array_key($data, ['PosUid', 'OrderName', 'OrderId', 'OrderNo', 'OrderMoney', 'OrderPrice']);
        $CallBackData['Sign'] = $Sign;
        if (empty($param)) return $CallBackData;
        return array_merge($CallBackData, $param);
    }

    /**
     * @param $param
     * @param $Key
     */
    public static function getSign($param, $Key)
    {
        $param = self::toArray($param);
        $OrderSign = self::md5Sign(self::array_key($param, ['OrderMoney', 'OrderNo', 'PosUid']), $Key);
        Func::Log(array_merge($param, ['OrderSign' => $OrderSign]), 'OrderSign');
        if ($param['Sign'] != $OrderSign) Func::ajax(-1, '系统签名不合法,请检查!');
    }

}