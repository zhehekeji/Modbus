<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker:   */

/*******************************************************************
 * @File: Timer.php
 * $Id: Timer.php v 1.0 2017-10-19 15:03:16 maxing $
 * $Author: maxing xm.crazyboy@gmail.com $
 * $Last modified: 2017-10-19 15:03:54 $
 * @brief
 *
 ******************************************************************/

namespace app\utility;

use Workerman\Lib\Timer as WTimer;

/**
    定时执行某个函数或者类方法。
 */
class Timer {
    /**
        添加一个定时器
        @param $callback 回调函数注意：如果回调函数是类的方法，则方法必须是public属性，
        参数为一个数组，第一个元素为对象，第二个元素为类的方法，如：array($object, $method)
        @param $args 回调函数的参数，必须为数组，数组元素为参数值
        @param $interval 多长时间执行一次，单位秒，支持小数，可以精确到0.001，即精确到毫秒级别。
        @param $loop 是否循环执行的，如果只想定时执行一次，则传递false，默认为true。
     */
    public static function add($callback, $args, $interval, $loop = true) {
        return WTimer::add($interval, $callback, $args, $loop);
    }

    public static function del($timerId) {
        return WTimer::del($timerId);
    }
}
