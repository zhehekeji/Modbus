<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker:   */

/*******************************************************************
 * @File: Cache.php
 * $Id: Cache.php v 1.0 2015-10-28 20:31:15 maxing $
 * $Author: maxing xm.crazyboy@gmail.com $
 * $Last modified: 2016-07-04 20:29:07 $
 * @brief
 *
 ******************************************************************/

namespace app\utility;

use Yii;

class Cache extends \Yii\base\Object {
    private static $prefix = "nrd_neizu_";

    public static function get($key) {
        if (Yii::$app->cache != null) {
            return Yii::$app->cache->get(self::$prefix . $key);
        }
        return null;
    }

    public static function set($key, $value, $duration = 0) {
        if (Yii::$app->cache != null) {
            Yii::$app->cache->set(self::$prefix . $key, $value, $duration);
        }
    }

    public static function delete($key) {
        if (Yii::$app->cache != null) {
            Yii::$app->cache->delete(self::$prefix . $key);
        }
    }
}
