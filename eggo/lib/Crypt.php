<?php


namespace Eggo\lib;


class Crypt
{

    /**
     *AES加密解密针对PHP7.0以上支持 by 20200316 无极
     * KEY密钥不足16位0自动填充
     */
    private static $AES_CIPHER_CBC = 'AES-128-CBC'; //Name of OpenSSL CBC
    private static $AES_CIPHER_ECB = 'AES-128-ECB'; //Name of OpenSSL ECB
    private static $AES_KEY_LEN = 16;               //128 bits
    private static $DES_CIPHER_CBC = 'DES-CBC';     //Name of OpenSSL ECB
    private static $DES_KEY_LEN = 8;                //8 bits

    /**
     * @param string $input 字符串内容
     * @param string $key 加密解密的KEY密钥16位
     * @param null $iv 加密偏移量默为空使用KEY
     * @param bool $mode 加密：{@link #ENCRYPT_MODE}，解密：{@link #DECRYPT_MODE}
     * @param bool $base64 加密字符格式: true为base64格式 false HEX16进制字符格式
     * @return false|string
     */
    public static function AES(string $input, string $key, $iv = null, bool $mode, bool $base64)
    {
        if (strlen($key) != Crypt::$AES_KEY_LEN) {
            $str = str_pad($key, Crypt::$AES_KEY_LEN, '0');      //0 pad to len 16
            $key = substr($str, 0, Crypt::$AES_KEY_LEN);              //truncate to 16 bytes
        }
        if ($iv == null) $iv = $key;
        if ($base64) {
            if ($mode) return base64_encode(openssl_encrypt($input, Crypt::$AES_CIPHER_CBC, $key, OPENSSL_RAW_DATA, $iv));
            else return openssl_decrypt(base64_decode($input), Crypt::$AES_CIPHER_CBC, $key, OPENSSL_RAW_DATA, $iv);
        } else {
            if ($mode) return strtoupper(bin2hex(openssl_encrypt($input, Crypt::$AES_CIPHER_CBC, $key, OPENSSL_RAW_DATA, $iv)));
            else return openssl_decrypt(hex2bin($input), Crypt::$AES_CIPHER_CBC, $key, OPENSSL_RAW_DATA, $iv);
        }
    }

    /**
     * @param string $input 加密解密的字符串
     * @param string $key 加密解密的KEY密钥16位
     * @param bool $mode 加密：{@link #ENCRYPT_MODE}，解密：{@link #DECRYPT_MODE}
     * @param bool $base64 加密字符格式: true为base64格式 false HEX16进制字符格式
     * @return false|string
     */
    public static function AES_ECB(string $input, string $key, bool $mode, bool $base64)
    {
        if (strlen($key) != Crypt::$AES_KEY_LEN) {
            $str = str_pad($key, Crypt::$AES_KEY_LEN, '0');      //0 pad to len 16
            $key = substr($str, 0, Crypt::$AES_KEY_LEN);              //truncate to 16 bytes
        }
        if ($base64) {
            if ($mode) return base64_encode(openssl_encrypt($input, Crypt::$AES_CIPHER_ECB, $key, OPENSSL_RAW_DATA));
            else return openssl_decrypt(base64_decode($input), Crypt::$AES_CIPHER_ECB, $key, OPENSSL_RAW_DATA);
        } else {
            if ($mode) return strtoupper(bin2hex(openssl_encrypt($input, Crypt::$AES_CIPHER_ECB, $key, OPENSSL_RAW_DATA)));
            else return openssl_decrypt(hex2bin($input), Crypt::$AES_CIPHER_ECB, $key, OPENSSL_RAW_DATA);
        }
    }


    /**
     * DES加密/解密
     * @param string $input 字符串内容
     * @param string $key 加密解密的KEY密钥8位
     * @param null $iv 加密偏移量默为空使用KEY
     * @param bool $mode 加密：{@link #ENCRYPT_MODE}，解密：{@link #DECRYPT_MODE}
     * @param bool $base64 加密字符格式: true为base64格式 false HEX16进制字符格式
     * @return false|string
     */
    public static function DES(string $input, string $key, $iv = null, bool $mode, bool $base64)
    {
        if (strlen($key) != Crypt::$DES_KEY_LEN) {
            $str = str_pad($key, Crypt::$DES_KEY_LEN, '0');      //0 pad to len 16
            $key = substr($str, 0, Crypt::$DES_KEY_LEN);              //truncate to 16 bytes
        }
        if ($iv == null) $iv = $key;
        if ($base64) {
            if ($mode) return base64_encode(openssl_encrypt($input, Crypt::$DES_CIPHER_CBC, $key, OPENSSL_RAW_DATA, $iv));
            else return openssl_decrypt(base64_decode($input), Crypt::$DES_CIPHER_CBC, $key, OPENSSL_RAW_DATA, $iv);
        } else {
            if ($mode) return strtoupper(bin2hex(openssl_encrypt($input, Crypt::$DES_CIPHER_CBC, $key, OPENSSL_RAW_DATA, $iv)));
            else return openssl_decrypt(hex2bin($input), Crypt::$DES_CIPHER_CBC, $key, OPENSSL_RAW_DATA, $iv);
        }
    }

}


