<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker:   */

/*******************************************************************
 * @File: Singleton.php
 * $Id: Singleton.php v 1.0 2017-07-19 14:08:22 maxing $
 * $Author: maxing xm.crazyboy@gmail.com $
 * $Last modified: 2018-07-25 15:24:31 $
 * @brief
 *
 ******************************************************************/

namespace app\utility;

abstract class Singleton {
    final protected function __construct() {
        $this->init();
    }

    final protected function __clone() { }

    final public static function instance() {
        if (static::$_instance === null) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }

    protected function init() {
    }
}
