<?php

namespace Eggo\lib;

use DateInterval;
use DatePeriod;
use DateTime;
use Exception;

class GoTime
{
    /**
     * @var
     */
    private static $_instance;

    final private function __construct()
    {
    }

    /**
     *
     * @author Eggo
     * @date 2022-02-26 13:51
     */
    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     *
     * @return mixed|static
     * @author Eggo
     * @date 2022-02-26 13:51
     */
    final public static function init()
    {
        return static::$_instance[static::class] ?? static::$_instance[static::class] = new static();
    }

    /**
     * 当天日期
     * @param bool $format
     * @param bool $unixTime
     * @return false|string
     */
    public static function get_today(bool $format, bool $unixTime)
    {
        $date = $format ? date('Y-m-d H:i:s') : date('Y-m-d');
        return $unixTime ? strtotime($date) : $date;
    }

    /**
     * 昨天日期
     * @param bool $format
     * @param bool $unixTime
     * @return false|string
     */
    public static function get_lastDay(bool $format, bool $unixTime)
    {
        $date = $format ? date('Y-m-d H:i:s', strtotime('-1 day')) : date('Y-m-d', strtotime('-1 day'));
        return $unixTime ? strtotime($date) : $date;
    }

    /**
     * 当天开始时间到结束时间
     * @param bool $unixTime
     * @return array
     */
    public static function is_todayTime(bool $unixTime): array
    {
        $timestamp = time();
        $beginTime = date('Y-m-d 00:00:00', $timestamp);
        $endTime = date('Y-m-d 23:59:59', $timestamp);
        return $unixTime ? [
            strtotime($beginTime),
            strtotime($endTime)
        ] : [
            $beginTime,
            $endTime
        ];
    }

    /**
     * 昨天开始时间到结束时间
     * @param bool $unixTime
     * @return array
     */
    public static function is_lastDayTime(bool $unixTime): array
    {
        $beginTime = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
        $endTime = mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 1;
        return $unixTime ? [
            $beginTime,
            $endTime
        ] : [
            date('Y-m-d 00:00:00', $beginTime),
            date('Y-m-d 23:59:59', $endTime)
        ];
    }


    /**
     * 返回本周开始和结束的时间
     *
     * @param bool $unixTime
     * @return array
     */
    public static function is_toWeekTime(bool $unixTime): array
    {
        $beginTime = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d') - date('w') + 1, date('Y')));
        $endTime = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d') - date('w') + 7, date('Y')));
        return $unixTime ? [
            strtotime($beginTime),
            strtotime($endTime)
        ] : [
            $beginTime,
            $endTime
        ];
    }

    /**
     * 返回上周开始和结束的时间
     *
     * @param bool $unixTime
     * @return array
     */
    public static function is_lastWeekTime(bool $unixTime): array
    {
        $beginTime = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1 - 7, date('Y'));
        $endTime = mktime(23, 59, 59, date('m'), date('d') - date('w') + 7 - 7, date('Y'));
        return $unixTime ? [
            $beginTime,
            $endTime
        ] : [
            date('Y-m-d H:i:s', $beginTime),
            date('Y-m-d H:i:s', $endTime)
        ];
    }


    /**
     * 本周7天所有日期
     * @param bool $unixTime
     * @return array
     */
    public static function get_toWeek(bool $unixTime): array
    {
        $timestamp = time();
        $week = date('w', $timestamp);
        $date = [];
        for ($i = 1; $i <= 7; $i++) if ($unixTime) {
            $date[$i] = strtotime(date('Y-m-d', strtotime('+' . ($i - $week) . 'days', $timestamp)));
        } else {
            $date[$i] = date('Y-m-d', strtotime('+' . ($i - $week) . 'days', $timestamp));
        }
        return $date;
    }


    /**
     * 最近7天所有日期
     * @param bool $unixTime
     * @return array
     */
    public static function get_7days(bool $unixTime): array
    {
        $timestamp = time();
        $date = [];
        for ($i = 1; $i <= 7; $i++) if ($unixTime) {
            $date[$i] = strtotime(date('Y-m-d', strtotime('+' . ($i - 7) . ' days', $timestamp)));
        } else {
            $date[$i] = date('Y-m-d', strtotime('+' . ($i - 7) . ' days', $timestamp));
        }
        return array_reverse($date);
    }

    /**
     * 最近3天所有日期
     * @param bool $unixTime
     * @return array
     */
    public static function get_3days(bool $unixTime): array
    {
        $timestamp = time();
        $date = [];
        for ($i = 1; $i <= 3; $i++) if ($unixTime) {
            $date[$i] = strtotime(date('Y-m-d', strtotime('+' . ($i - 3) . ' days', $timestamp)));
        } else $date[$i] = date('Y-m-d', strtotime('+' . ($i - 3) . ' days', $timestamp));
        return array_reverse($date);
    }

    /**
     * 返回本月开始和结束的时间
     * @param bool $unixTime
     * @return array
     */
    public static function is_monthTime(bool $unixTime): array
    {
        $beginTime = mktime(0, 0, 0, date('m'), 1, date('Y'));
        $endTime = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
        return $unixTime ? [
            $beginTime,
            $endTime
        ] : [
            date('Y-m-d 00:00:00', $beginTime),
            date('Y-m-d 23:39:59', $endTime)
        ];
    }

    /**
     * 返回上个月开始和结束的时间戳
     *
     * @param bool $unixTime
     * @return array
     */
    public static function is_lastMonthTime(bool $unixTime): array
    {
        $beginTime = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));
        $endTime = mktime(23, 59, 59, date('m') - 1, date('t', $beginTime), date('Y'));
        return $unixTime ? [
            $beginTime,
            $endTime
        ] : [
            date('Y-m-d 00:00:00', $beginTime),
            date('Y-m-d 23:39:59', $endTime)
        ];
    }

    /**
     * 获取季度的开始结束时间
     * @param bool $unixTime
     * @return array
     */
    public static function is_Quarter(bool $unixTime): array
    {
        $season = ceil((date('n')) / 3);   //当月是第几季度
        $beginTime = mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y'));
        $endTime = mktime(23, 59, 59, $season * 3, date('t', mktime(0, 0, 0, $season * 3, 1, date('Y'))), date('Y'));
        return $unixTime ? [
            $beginTime,
            $endTime
        ] : [
            date('Y-m-d H:i:s', $beginTime),
            date('Y-m-d H:i:s', $endTime)
        ];
    }

    /**
     * 上季度开始结束时间
     * @param bool $unixTime
     * @return array
     */
    public static function is_lastQuarter(bool $unixTime): array
    {
        $season = ceil((date('n')) / 3) - 1;  //上季度是第几季度
        $beginTime = mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y'));
        $endTime = mktime(23, 59, 59, $season * 3, date('t', mktime(0, 0, 0, $season * 3, 1, date("Y"))), date('Y'));
        return $unixTime ? [
            $beginTime,
            $endTime
        ] : [
            date('Y-m-d H:i:s', $beginTime),
            date('Y-m-d H:i:s', $endTime)
        ];
    }

    /**
     * 返回今年开始和结束的时间戳
     *
     * @param bool $unixTime
     * @return array
     */
    public static function is_yearTime(bool $unixTime): array
    {
        $beginTime = mktime(0, 0, 0, 1, 1, date('Y'));
        $endTime = mktime(23, 59, 59, 12, 31, date('Y'));
        return $unixTime ? [
            $beginTime,
            $endTime
        ] : [
            date('Y-m-d 00:00:00', $beginTime),
            date('Y-m-d 23:39:59', $endTime)
        ];
    }

    /**
     * 返回去年开始和结束的时间戳
     *
     * @param bool $unixTime
     * @return array
     */
    public static function is_lastYearTime(bool $unixTime): array
    {
        $year = date('Y') - 1;
        $beginTime = mktime(0, 0, 0, 1, 1, $year);
        $endTime = mktime(23, 59, 59, 12, 31, $year);
        return $unixTime ? [
            $beginTime,
            $endTime
        ] : [
            date('Y-m-d 00:00:00', $beginTime),
            date('Y-m-d 23:39:59', $endTime)
        ];
    }

    /**
     *  获取两个时间之间的日期
     * @param $beginTime
     * @param $endTime
     * @return array
     */
    public static function getDatesBetweenTwoDays($beginTime, $endTime): array
    {
        $date = [];
        try {
            $begin = new DateTime($beginTime);
            $end = new DateTime($endTime);
            $end = $end->modify('+1 day');
            $interval = new DateInterval('P1D');
            $dateRange = new DatePeriod($begin, $interval, $end);
            foreach ($dateRange as $Range) $date[] = $Range->format('Y-m-d');
        } catch (Exception $e) {
        }
        return array_reverse($date);
    }

    /**
     * 计算开始到结束返回天数多少
     * @param $date_start
     * @param $date_end
     * @return float|int|string
     */
    public static function getDateDaysNum($date_start, $date_end)
    {
        if (!$date_start || !$date_end) return 0;
        if (class_exists('DateTime')) {
            try {
                $date_start = new DateTime($date_start);
                $date_end = new DateTime($date_end);
            } catch (Exception $e) {
            }
            $diff = $date_end->diff($date_start);
            return $diff->format('%a');
        } else {
            return abs(round((strtotime($date_start) - strtotime($date_end)) / 86400));
        }
    }

    /**
     * 计算任意时间中间段天数
     * @param $beginTime
     * @param $endTime
     * @return float|int
     */
    public static function is_allDays($beginTime, $endTime)
    {
        $time = strtotime($endTime) - strtotime($beginTime);
        return $time / (3600 * 24);
    }

    /**
     * 返回几天前的时间戳
     *
     * @param int $day
     * @return int
     */
    public static function daysAgo(int $day = 1)
    {
        $nowTime = time();
        return $nowTime - self::daysToSecond($day);
    }

    /**
     * 返回几天后的时间戳
     *
     * @param int $day
     * @return int
     */
    public static function daysAfter(int $day = 1)
    {
        $nowTime = time();
        return $nowTime + self::daysToSecond($day);
    }

    /**
     * 天数转换成秒数
     *
     * @param int $day
     * @return int
     */
    public static function daysToSecond(int $day = 1)
    {
        return $day * 86400;
    }

    /**
     * 周数转换成秒数
     *
     * @param int $week
     * @return int
     */
    public static function weekToSecond(int $week = 1)
    {
        return self::daysToSecond() * 7 * $week;
    }


}