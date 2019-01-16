<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker:   */

/*******************************************************************
 * @File: Redis.php
 * $Id: Redis.php v 1.0 2016-06-07 20:36:08 maxing $
 * $Author: maxing xm.crazyboy@gmail.com $
 * $Last modified: 2018-08-03 10:24:14 $
 * @brief
 *
 ******************************************************************/

namespace app\utility;

use app\utility\Singleton;

class Redis extends Singleton {
    protected static $_instance = null;
    private $_redis = null;

    protected function init() {
        $host = \Yii::$app->params['redis']['hostname'];
        $port = \Yii::$app->params['redis']['port'];
        $pass = \Yii::$app->params['redis']['password'];
        $this->_redis = new \Redis();
        $this->_redis->pconnect($host, $port);
        if ($pass != '') {
            $this->_redis->auth($pass);
        }
    }

    public function __call($name, $args) {
        if (method_exists($this->_redis, $name)) {
            return call_user_func_array(array($this->_redis, $name), $args);
            //return $this->_redis->$name($args);
        }
        else {
            throw new \Exception("Method $name not support!");
        }
    }
}
