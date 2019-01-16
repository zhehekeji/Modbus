<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker:   */

/*******************************************************************
 * @File: String.php
 * $Id: String.php v 1.0 2017-09-29 13:44:48 maxing $
 * $Author: maxing xm.crazyboy@gmail.com $
 * $Last modified: 2017-10-19 14:16:56 $
 * @brief
 *
 ******************************************************************/

namespace app\utility;

class String {
    public static function bin2Str($str) {
        $s = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $s .= sprintf('%02x', ord($str[$i]));
        }
        $s = strtoupper($s);
        return $s;
    }

    public static function bcdEncode($str) {
        $s = '';
        for ($i = 0; $i < strlen($str); $i+= 2) {
            $s .= chr(hexdec($str[$i] . $str[$i+1]));
        }
        return $s;
    }

    public static function bcdDecode($str) {
        $s = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $s .= sprintf('%02X', ord($str[$i]));
        }
        return $s;
    }
}
