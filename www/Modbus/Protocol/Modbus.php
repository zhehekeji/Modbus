<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker:   */

/*******************************************************************
 * @File: Modbus.php
 * $Id: Modbus.php v 1.0 2018-07-24 16:58:08 maxing $
 * $Author: maxing xm.crazyboy@gmail.com $
 * $Last modified: 2018-08-01 10:56:39 $
 * @brief
 *
 ******************************************************************/

namespace app\Modbus\Protocol;

use app\utility\String;
use app\Modbus\Protocol\Message;
use app\Modbus\Logger;

class Modbus {
    public static function input($buffer) {
        $bufferLen = strlen($buffer);

        // 包长度至少为6字节
        if ($bufferLen <= 6) {
            return 0;
        }

        $dataLen = unpack('nlen', $buffer[4] . $buffer[5])['len'];
        $packLen = 6 + $dataLen;
        // 如果包长度大于缓存长度，则继续等待后续字节流
        if ($packLen > $bufferLen) {
            return 0;
        }

        return $packLen;
    }

    public static function decode($buffer) {
        Logger::info('[' . __METHOD__ . ']', array('buffer' => String::bin2Str($buffer)));
        $msg = new Message();
        $msg->isRequest = false;
        $msg->decode($buffer);
        return $msg;
    }

    public static function encode($msg) {
        $buffer = $msg->encode();
        Logger::info('[' . __METHOD__ . ']', array('buffer' => String::bin2Str($buffer)));
        return $buffer;
    }
}
