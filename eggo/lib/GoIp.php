<?php
declare(strict_types=1);

namespace Eggo\lib;

use Exception;

defined('INDEX_BLOCK_LENGTH') or define('INDEX_BLOCK_LENGTH', 12);
defined('TOTAL_HEADER_LENGTH') or define('TOTAL_HEADER_LENGTH', 8192);

class GoIp
{
    /**
     * @var array
     */
    private static $instance = [];

    /**
     * Singletons should not be cloneable.
     */
    final private function __clone()
    {
    }

    /**
     * db file handler
     */
    protected static $dbFileHandler = NULL;

    /**
     * header block info
     */
    protected static $HeaderSip = NULL;
    /**
     * @var null
     */
    protected static $HeaderPtr = NULL;
    /**
     * @var int
     */
    protected static $headerLen = 0;

    /**
     * super block index info
     */
    protected static $firstIndexPtr = 0;
    /**
     * @var int
     */
    protected static $lastIndexPtr = 0;
    /**
     * @var int
     */
    protected static $totalBlocks = 0;

    /**
     * for memory mode only
     *  the original db binary string
     */
    protected static $dbBinStr = NULL;
    // protected static $dbFile = 'ip2db/ip2region.db';
    /**
     * @var mixed|string
     */
    protected static $dbFile;

    /**
     * @param null $ipdbFile
     */
    final private function __construct($ipdbFile = null)
    {
        $Path = ROOT . DIRECTORY_SEPARATOR;
        self::$dbFile = is_null($ipdbFile) ? $Path . 'vendor/zoujingli/ip2region/ip2region.db' : $ipdbFile;
    }

    /**
     *
     * @param ...$args
     * @return mixed|static
     * @author Eggo
     * @date 2022-02-26 1:39
     */
    final public static function init(...$args)
    {
        return static::$instance[static::class] ?? static::$instance[static::class] = new static(...$args);
    }

    /**
     *
     * @param $ip
     * @return array|null
     * @throws Exception
     * @author Eggo
     * @date 2022-02-26 1:15
     */
    public static function get($ip): ?array
    {
        self::isIp($ip);
        //check and load the binary string for the first time
        if (self::$dbBinStr == NULL) {
            self::$dbBinStr = file_get_contents(self::$dbFile);
            if (self::$dbBinStr == false) throw new Exception(sprintf('Fail to open the dbFile %s', self::$dbFile));
            self::$firstIndexPtr = self::getLong(self::$dbBinStr, 0);
            self::$lastIndexPtr = self::getLong(self::$dbBinStr, 4);
            self::$totalBlocks = (self::$lastIndexPtr - self::$firstIndexPtr) / INDEX_BLOCK_LENGTH + 1;
        }
        if (is_string($ip)) $ip = self::safeIp2long($ip);
        //binary search to define the data
        $l = 0;
        $h = self::$totalBlocks;
        $dataPtr = 0;
        while ($l <= $h) {
            $m = (($l + $h) >> 1);
            $p = self::$firstIndexPtr + $m * INDEX_BLOCK_LENGTH;
            $sip = self::getLong(self::$dbBinStr, $p);
            if ($ip < $sip) $h = $m - 1; else {
                $eip = self::getLong(self::$dbBinStr, $p + 4);
                if ($ip > $eip) $l = $m + 1; else {
                    $dataPtr = self::getLong(self::$dbBinStr, $p + 8);
                    break;
                }
            }
        }
        //not matched just stop it here
        if ($dataPtr == 0) return NULL;
        //get the data
        $dataLen = (($dataPtr >> 24) & 0xFF);
        $dataPtr = ($dataPtr & 0x00FFFFFF);
        $arr = explode('|', substr(self::$dbBinStr, $dataPtr + 4, $dataLen - 4));
        unset($arr[1]);
        $Region = implode('|', $arr);
        return array(
            'Region' => sprintf('%s|%s', $Region, long2ip($ip)),
            'Prov' => $arr[2],
            'City' => $arr[3],
            'CityId' => self::getLong(self::$dbBinStr, $dataPtr)
        );
    }

    /**
     *
     * @param $ip
     * @return array|null
     * @throws Exception
     * @author Eggo
     * @date 2022-02-26 1:17
     */
    public static function getBiSearch($ip): ?array
    {
        self::isIp($ip);
        //check and con_ver the ip address
        if (is_string($ip)) $ip = self::safeIp2long($ip);
        if (self::$totalBlocks == 0) {
            //check and open the original db file
            if (self::$dbFileHandler == NULL) {
                self::$dbFileHandler = fopen(self::$dbFile, 'r');
                if (self::$dbFileHandler == false) throw new Exception(sprintf('Fail to open the dbFile %s', self::$dbFile));
            }
            fseek(self::$dbFileHandler, 0);
            $superBlock = fread(self::$dbFileHandler, 8);
            self::$firstIndexPtr = self::getLong($superBlock, 0);
            self::$lastIndexPtr = self::getLong($superBlock, 4);
            self::$totalBlocks = (self::$lastIndexPtr - self::$firstIndexPtr) / INDEX_BLOCK_LENGTH + 1;
        }
        //binary search to define the data
        $l = 0;
        $h = self::$totalBlocks;
        $dataPtr = 0;
        while ($l <= $h) {
            $m = (($l + $h) >> 1);
            $p = $m * INDEX_BLOCK_LENGTH;
            fseek(self::$dbFileHandler, self::$firstIndexPtr + $p);
            $buffer = fread(self::$dbFileHandler, INDEX_BLOCK_LENGTH);
            $sip = self::getLong($buffer, 0);
            if ($ip < $sip) $h = $m - 1; else {
                $eip = self::getLong($buffer, 4);
                if ($ip > $eip) $l = $m + 1; else {
                    $dataPtr = self::getLong($buffer, 8);
                    break;
                }
            }
        }
        //not matched just stop it here
        if ($dataPtr == 0) return NULL;
        //get the data
        $dataLen = (($dataPtr >> 24) & 0xFF);
        $dataPtr = ($dataPtr & 0x00FFFFFF);
        fseek(self::$dbFileHandler, $dataPtr);
        $data = fread(self::$dbFileHandler, $dataLen);

        $arr = explode('|', substr($data, 4));
        unset($arr[1]);
        $Region = implode('|', $arr);
        return array(
            'Region' => $Region . '|' . long2ip($ip),
            'Prov' => $arr[2],
            'City' => $arr[3],
            'CityId' => self::getLong($data, 0)
        );
    }


    /**
     * @throws Exception
     */
    public static function getBtSearch($ip): ?array
    {
        self::isIp($ip);
        if (is_string($ip)) $ip = self::safeIp2long($ip);
        //check and load the header
        if (self::$HeaderSip == NULL) {
            //check and open the original db file
            if (self::$dbFileHandler == NULL) {
                self::$dbFileHandler = fopen(self::$dbFile, 'r');
                if (self::$dbFileHandler == false) throw new Exception(sprintf('Fail to open the dbFile %s', self::$dbFile));
            }
            fseek(self::$dbFileHandler, 8);
            $buffer = fread(self::$dbFileHandler, TOTAL_HEADER_LENGTH);
            //fill the header
            $idx = 0;
            self::$HeaderSip = array();
            self::$HeaderPtr = array();
            for ($i = 0; $i < TOTAL_HEADER_LENGTH; $i += 8) {
                $startIp = self::getLong($buffer, $i);
                $dataPtr = self::getLong($buffer, $i + 4);
                if ($dataPtr == 0) break;
                self::$HeaderSip[] = $startIp;
                self::$HeaderPtr[] = $dataPtr;
                $idx++;
            }
            self::$headerLen = $idx;
        }
        //1. define the index block with the binary search
        $l = 0;
        $h = self::$headerLen;
        $sp_tr = 0;
        $ep_tr = 0;
        while ($l <= $h) {
            $m = (($l + $h) >> 1);
            //PerFeTc matched, just return it
            if ($ip == self::$HeaderSip[$m]) {
                if ($m > 0) {
                    $sp_tr = self::$HeaderPtr[$m - 1];
                    $ep_tr = self::$HeaderPtr[$m];
                } else {
                    $sp_tr = self::$HeaderPtr[$m];
                    $ep_tr = self::$HeaderPtr[$m + 1];
                }
                break;
            }
            //less the middle value
            if ($ip < self::$HeaderSip[$m]) {
                if ($m == 0) {
                    $sp_tr = self::$HeaderPtr[$m];
                    $ep_tr = self::$HeaderPtr[$m + 1];
                    break;
                } else if ($ip > self::$HeaderSip[$m - 1]) {
                    $sp_tr = self::$HeaderPtr[$m - 1];
                    $ep_tr = self::$HeaderPtr[$m];
                    break;
                }
                $h = $m - 1;
            } else {
                if ($m == self::$headerLen - 1) {
                    $sp_tr = self::$HeaderPtr[$m - 1];
                    $ep_tr = self::$HeaderPtr[$m];
                    break;
                } else if ($ip <= self::$HeaderSip[$m + 1]) {
                    $sp_tr = self::$HeaderPtr[$m];
                    $ep_tr = self::$HeaderPtr[$m + 1];
                    break;
                }
                $l = $m + 1;
            }
        }
        //match nothing just stop it
        if ($sp_tr == 0) return NULL;
        //2. search the index blocks to define the data
        $blockLen = $ep_tr - $sp_tr;
        fseek(self::$dbFileHandler, $sp_tr);
        $index = fread(self::$dbFileHandler, $blockLen + INDEX_BLOCK_LENGTH);

        $dataPtr = 0;
        $l = 0;
        $h = $blockLen / INDEX_BLOCK_LENGTH;
        while ($l <= $h) {
            $m = (($l + $h) >> 1);
            $p = (int)($m * INDEX_BLOCK_LENGTH);
            $sip = self::getLong($index, $p);
            if ($ip < $sip) $h = $m - 1; else {
                $eip = self::getLong($index, $p + 4);
                if ($ip > $eip) $l = $m + 1; else {
                    $dataPtr = self::getLong($index, $p + 8);
                    break;
                }
            }
        }
        //not matched
        if ($dataPtr == 0) return NULL;
        //3. get the data
        $dataLen = (($dataPtr >> 24) & 0xFF);
        $dataPtr = ($dataPtr & 0x00FFFFFF);
        fseek(self::$dbFileHandler, $dataPtr);
        $data = fread(self::$dbFileHandler, $dataLen);

        $arr = explode('|', substr($data, 4));
        unset($arr[1]);
        $Region = implode('|', $arr);
        return array(
            'Region' => sprintf('%s|%s', $Region, long2ip($ip)),
            'Prov' => $arr[2],
            'City' => $arr[3],
            'CityId' => self::getLong($data, 0)
        );
    }

    /**
     * @param $ip
     * @return int|string
     */
    protected static function safeIp2long($ip)
    {
        $ip = ip2long($ip);
        // convert signed int to unsigned int if on 32bit operating system
        if ($ip < 0 && PHP_INT_SIZE == 4) $ip = sprintf('%u', $ip);
        return $ip;
    }

    /**
     *
     * @param $ip
     * @author Eggo
     * @date 2022-02-26 1:15
     */
    protected static function isIp($ip)
    {
        $long = ip2long($ip);
        if ($long == NULL || $long == -1) exit(0);
    }

    /**
     * @param $b
     * @param $offset
     * @return int|string
     */
    protected static function getLong($b, $offset)
    {
        $val = (
            (ord($b[$offset++])) |
            (ord($b[$offset++]) << 8) |
            (ord($b[$offset++]) << 16) |
            (ord($b[$offset]) << 24)
        );
        // convert signed int to unsigned int if on 32bit operating system
        if ($val < 0 && PHP_INT_SIZE == 4) $val = sprintf('%u', $val);
        return $val;
    }

    /**
     * @return float|int
     */
    protected static function getTime()
    {
        return (microtime(true) * 1000);
    }

    /**
     * destruct method, resource destroy
     */
    public function __destruct()
    {
        if (self::$dbFileHandler != NULL) fclose(self::$dbFileHandler);
        self::$dbBinStr = NULL;
        self::$HeaderSip = NULL;
        self::$HeaderPtr = NULL;
    }
}
