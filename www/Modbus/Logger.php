<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker:   */

/*******************************************************************
 * @File: Logger.php
 * $Id: Logger.php v 1.0 2016-05-06 17:18:02 maxing $
 * $Author: maxing xm.crazyboy@gmail.com $
 * $Last modified: 2018-08-01 11:53:50 $
 * @brief
 *
 ******************************************************************/

namespace app\Modbus;

class Logger {
    const ERROR     = 0x01;
    const WARNING   = 0x02;
    const INFO      = 0x04;
    const DEBUG     = 0x08;
    const ALL       = 0xFFFF;

    static $level = 0x0F;

    private static function buildParamsStr($params) {
        $str = '';
        foreach ($params as $k => $v) {
            $str .= "[$k:$v]";
        }
        return $str;
    }

    public static function error($str, $params = array()) {
        $str .= self::buildParamsStr($params);
        if (Logger::ERROR & self::$level) {
            //echo "[error]$str";
            \Yii::error($str, 'modbus');
            self::flush(true);
        }
    }

    public static function warning($str, $params = array()) {
        $str .= self::buildParamsStr($params);
        if (Logger::WARNING & self::$level) {
            //echo "[warning]$str";
            \Yii::warning($str, 'modbus');
            self::flush(true);
        }
    }

    public static function info($str, $params = array()) {
        $str .= self::buildParamsStr($params);
        if (Logger::INFO & self::$level) {
            //echo "[info]$str";
            \Yii::info($str, 'modbus');
            self::flush(true);
        }
    }

    public static function debug($str, $params = array()) {
        $str .= self::buildParamsStr($params);
        if (Logger::DEBUG & self::$level) {
            //echo "[debug]$str\n";
            \Yii::trace($str, 'modbus');
            self::flush(true);
        }
    }

    public static function flush($force = false) {
        \Yii::getLogger()->flush($force);
    }
}
