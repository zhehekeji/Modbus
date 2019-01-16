<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker:   */

/*******************************************************************
 * @File: DataDao.php
 * $Id: DataDao.php v 1.0 2016-07-05 23:03:41 maxing $
 * $Author: maxing xm.crazyboy@gmail.com $
 * $Last modified: 2018-08-03 12:50:06 $
 * @brief
 *
 ******************************************************************/

namespace app\Modbus\Dao;

use app\Modbus\Logger;
use app\utility\Redis;
use app\utility\MeasurePointManager as MPM;

class DataDao {
    const CACHE_PREFIX = 'modbus_';

    private function key($sn) {
        return DataDao::CACHE_PREFIX . strtoupper($sn);
    }

    public function updateData($sn, $dataList) {
        $key = $this->key($sn);
        Redis::instance()->hMSet($key, $dataList);
    }

    public function getData($sn, $tpList = []) {
        $key = $this->key($sn);
        $redis = Redis::instance();
        $dataList = array();
        if (empty($tpList)) {
            $dataList = $redis->hGetAll($key);
        }
        else {
            $dataList = $redis->hMGet($key, $tpList);
        }
        if ($dataList === false) {
            $dataList = array();
        }
        $ret = array();
        foreach ($dataList as $tp => $value) {
            $tpInfo = MPM::instance()->info($tp);
            switch ($tpInfo[MPM::IDX_VALUETYPE]) {
            case MPM::Float:
                $ret[$tp] = round($value, $tpInfo[MPM::IDX_PRECISION]);
                break;
            case MPM::Uint32:
                $ret[$tp] = intval($value);
                break;
            case MPM::Uint16:
                $ret[$tp] = intval($value);
                break;
            case MPM::Uint8:
            case MPM::Int8:
                $ret[$tp] = intval($value);
                break;
            case MPM::String:
                $ret[$tp] = $value;
                break;
            default:
                $ret[$tp] = $value;
                break;
            }
        }
        return $ret;
    }

    public function clear($sn) {
        $key = $this->key($sn);
        return Redis::instance()->delete($key);
    }
}
