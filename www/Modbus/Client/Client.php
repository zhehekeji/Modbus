<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker:   */

/*******************************************************************
 * @File: Client.php
 * $Id: Client.php v 1.0 2017-09-29 15:22:21 maxing $
 * $Author: maxing xm.crazyboy@gmail.com $
 * $Last modified: 2018-12-20 17:17:09 $
 * @brief
 *
 ******************************************************************/

namespace app\Modbus\Client;

use app\utility\String;
use app\utility\Timer;
use app\Modbus\MeasurePointManager;
use app\Modbus\Protocol\Message;
use app\Modbus\Logger;
use app\Modbus\Client\ClientManager;
use app\Modbus\Dao\DataDao;

class Client {
    const STEP_INFO             = 0;
    const STEP_PACK_CURR        = 1;
    const STEP_PACK_TEMP_1      = 2;
    const STEP_PACK_TEMP_2      = 3;
    const STEP_PACK_TC_STATUS   = 4;
    const STEP_PACK_AH          = 5;
    const STEP_PACK_CURR_FAC    = 6;
    const STEP_PACK_CONNECTION  = 7;
    const STEP_CELL_VOLT        = 8;
    const STEP_CELL_TEMP        = 9;
    const STEP_CELL_RES         = 10;
    const STEP_CELL_TEMP_ALARM  = 11;
    const STEP_CELL_RES_ALARM   = 12;
    const STEP_CELL_TA_STATUS   = 13;

    private $_id;
    private $_sn;
    // 实时数据
    private $_realData;
    private $_updateTime = -1;
    private $_timer = -1;

    // 设备信息
    private $_packCnt;
    private $_cellNumList = array();

    // 当前的状态
    private $_currStep;
    private $_currPack;
    private $_offset;

    private $_fetchInterval = 600; // 正常情况下，10分钟采集一次
    private $_fetchIntervalEmergency = 120; // 放电情况下，提升采集频率为2分钟一次
    private $_lastFetchTime = 0;
    private $_nextFetchTime = 0;

    public function __construct($id) {
        $this->_id = $id;
        $msg = new Message();
        $msg->func = Message::FUNC_INFO;
        $msg->subFunc = 0x80;
        ClientManager::instance()->sendMessage($this->_id, $msg);
    }

    public function id() {
        return $this->_id;
    }

    public function sn() {
        return $this->_sn;
    }

    public function updateTime() {
        return $this->_updateTime;
    }

    public function handleMessage($msg) {
        switch ($msg->func) {
        case Message::FUNC_INFO:
            $this->_sn   = '00' . substr($msg->sn, 0, 12); // sn补足14位
            $this->initStep();
            $this->scheduleDataFetch();
            break;
        case Message::FUNC_MODBUS_READ:
            $data = $this->parseDataMsg($msg);
            $this->updateRealData($data);
            $this->saveData($data);
            $this->updateStep();
            $this->stepLoop();
            break;
        default:
            break;
        }
    }

    private function scheduleDataFetch() {
        $this->_timer = Timer::add(array($this, 'initStep'), array(), 60, true);
    }

    public function clearTimer() {
        if ($this->_timer != -1) {
            Timer::del($this->_timer);
        }
    }

    public function initStep() {
        $t = time();
        $t = $t - $t % 60; // 对齐到分钟
        if ($this->_nextFetchTime > $t) {
            return;
        }

        $this->_lastFetchTime = $t;
        $this->_nextFetchTime = $t + $this->_fetchInterval;
        $this->_currStep = 0;
        $this->_currPack = 0;
        $this->_offset   = 0;

        Logger::info('[' . __METHOD__ . ']', array('sn' => $this->_sn, 'lastUpdate' => $this->_updateTime));
        $this->stepLoop();
    }

    private function stepLoop() {
        $msg            = new Message();
        $msg->tagH      = $this->_currStep;
        $msg->func      = Message::FUNC_MODBUS_READ;
        $mpm            = MeasurePointManager::instance();
        switch ($this->_currStep) {
        case self::STEP_INFO:
            $msg->startAddr = 1;
            $msg->addrLen   = 21;
            break;
        case self::STEP_PACK_CURR:
            $msg->startAddr = 50;
            $msg->addrLen   = array_value($this->_realData, $mpm->mp(DT_SYS_PACK_NUM), 0) * 2;
            break;
        case self::STEP_PACK_TEMP_1:
            $msg->startAddr = 100;
            $msg->addrLen   = array_value($this->_realData, $mpm->mp(DT_SYS_PACK_NUM), 0) * 2;
            break;
        case self::STEP_PACK_TEMP_2:
            $msg->startAddr = 150;
            $msg->addrLen   = array_value($this->_realData, $mpm->mp(DT_SYS_PACK_NUM), 0) * 2;
            break;
        case self::STEP_PACK_TC_STATUS:
            $msg->startAddr = 200;
            $msg->addrLen   = array_value($this->_realData, $mpm->mp(DT_SYS_PACK_NUM), 0);
            break;
        case self::STEP_PACK_AH:
            $msg->startAddr = 250;
            $msg->addrLen   = array_value($this->_realData, $mpm->mp(DT_SYS_PACK_NUM), 0) * 2;
            break;
        case self::STEP_PACK_CURR_FAC:
            $msg->startAddr = 300;
            $msg->addrLen   = array_value($this->_realData, $mpm->mp(DT_SYS_PACK_NUM), 0) * 2;
            break;
        case self::STEP_PACK_CONNECTION:
            $msg->startAddr = 350;
            $msg->addrLen   = 32;
            break;
        case self::STEP_CELL_VOLT:
            $msg->tagL      = $this->_currPack;
            $msg->startAddr = 1000 + $this->_offset * 2;
            $msg->addrLen   = array_value($this->_realData, $mpm->mp(DT_BT_CELL_NUM, $this->_currPack), 0) * 2;
            break;
        case self::STEP_CELL_TEMP:
            $msg->tagL      = $this->_currPack;
            $msg->startAddr = 2000 + $this->_offset * 2;
            $msg->addrLen   = array_value($this->_realData, $mpm->mp(DT_BT_CELL_NUM, $this->_currPack), 0) * 2;
            break;
        case self::STEP_CELL_RES:
            $msg->tagL      = $this->_currPack;
            $msg->startAddr = 3000 + $this->_offset * 2;
            $msg->addrLen   = array_value($this->_realData, $mpm->mp(DT_BT_CELL_NUM, $this->_currPack), 0) * 2;
            break;
        case self::STEP_CELL_TEMP_ALARM:
            $msg->tagL      = $this->_currPack;
            $msg->startAddr = 4000 + $this->_offset;
            $msg->addrLen   = $this->_realData[$mpm->mp(DT_BT_CELL_NUM, $this->_currPack)];
        case self::STEP_CELL_RES_ALARM:
            $msg->tagL      = $this->_currPack;
            $msg->startAddr = 4500 + $this->_offset;
            $msg->addrLen   = $this->_realData[$mpm->mp(DT_BT_CELL_NUM, $this->_currPack)];
        case self::STEP_CELL_TA_STATUS:
            $msg->tagL      = $this->_currPack;
            $msg->startAddr = 5000 + $this->_offset;
            $msg->addrLen   = $this->_realData[$mpm->mp(DT_BT_CELL_NUM, $this->_currPack)];
            break;
        default:
            $msg = null;
        }
        if ($msg != null) {
            ClientManager::instance()->sendMessage($this->_id, $msg);
        }
    }

    private function updateStep() {
        if ($this->_currStep <= self::STEP_PACK_CONNECTION) {
            $this->_currStep     += 1;
            $this->_currPack      = 0;
            $this->_offset        = 0;
        }
        else {
            if ($this->_currPack + 1 < $this->_packCnt) {
                // cellNumList记录的电池的数量（也就是TA的数量），而PLC则包含了TC，所以要加1
                $this->_offset   += $this->_cellNumList[$this->_currPack] + 1;
                $this->_currPack += 1;
            }
            else {
                $this->_currStep += 1;
                $this->_currPack  = 0;
                $this->_offset    = 0;
            }
        }
    }

    private function updateRealData($data) {
        foreach ($data as $k => $v) {
            $this->_realData[$k] = $v;
        }

        if (!empty($data)) {
            $mpm = MeasurePointManager::instance();
            $this->_updateTime = array_value($this->_realData, $mpm->mp(DT_SYS_UPDATE_TIME), $this->_updateTime);
        }
    }

    private function parseDataMsg($msg) {
        $step = $msg->tagH;
        $ret = array();
        switch ($step) {
        case self::STEP_INFO:
            $ret = $this->parseDeviceInfoData($msg);
            break;
        case self::STEP_PACK_CURR:
            $ret = $this->parsePackData($msg, DT_BT_PACK_CURR, 'f', 4);
            break;
        case self::STEP_PACK_TEMP_1:
            $ret = $this->parsePackData($msg, DT_BT_ENV_TEMP_1, 'f', 4);
            break;
        case self::STEP_PACK_TEMP_2:
            $ret = $this->parsePackData($msg, DT_BT_ENV_TEMP_2, 'f', 4);
            break;
        case self::STEP_PACK_TC_STATUS:
            $ret = $this->parsePackData($msg, DT_BT_TC_STATUS, 'n', 2);
            break;
        case self::STEP_PACK_AH:
            $ret = $this->parsePackData($msg, DT_BT_AH, 'f', 4);
            break;
        case self::STEP_PACK_CURR_FAC:
            $ret = $this->parsePackData($msg, DT_BT_CURR_FACTOR, 'f', 4);
            break;
        case self::STEP_PACK_CONNECTION:
            $ret = $this->parseConnectionBitData($msg);
            break;
        case self::STEP_CELL_VOLT:
            $ret = $this->parseCellData($msg, DT_BT_CELL_UNIT_VOLT, $this->_cellNumList[$this->_currPack], 'f', 4);
            break;
        case self::STEP_CELL_TEMP:
            $ret = $this->parseCellData($msg, DT_BT_CELL_UNIT_TEMP, $this->_cellNumList[$this->_currPack], 'f', 4);
            break;
        case self::STEP_CELL_RES:
            $ret = $this->parseCellData($msg, DT_BT_CELL_UNIT_R, $this->_cellNumList[$this->_currPack], 'N', 4);
            break;
        case self::STEP_CELL_TEMP_ALARM:
            $ret = $this->parseCellData($msg, DT_BT_CELL_UNIT_TEMP_ALARM, $this->_cellNumList[$this->_currPack], 'n', 2);
            break;
        case self::STEP_CELL_RES_ALARM:
            $ret = $this->parseCellData($msg, DT_BT_CELL_UNIT_R_ALARM, $this->_cellNumList[$this->_currPack], 'n', 2);
            break;
        case self::STEP_CELL_TA_STATUS:
            $ret = $this->parseCellData($msg, DT_BT_CELL_UNIT_TA_STATUS, $this->_cellNumList[$this->_currPack], 'n', 2);
            break;
        }

        // 补充记录时间
        if (!empty($ret)) {
            $mpm = MeasurePointManager::instance();
            $ret[$mpm->mp(DT_SYS_UPDATE_TIME)] = date('Y-m-d H:i:s', time());
        }

        // 打印日志
        $logRet = array('sn' => $this->_sn);
        foreach ($ret as $k => $v) {
            $logRet[sprintf('0X%0X', $k)] = $v;
        }
        Logger::info('[' . __METHOD__ . ']', $logRet);

        return $ret;
    }

    private function parseDeviceInfoData($msg) {
        $ret = array();
        $mpm = MeasurePointManager::instance();
        $data = $msg->data;
        $this->_packCnt = $ret[$mpm->mp(DT_SYS_PACK_NUM)] = unpack('nv', $data)['v'];
        for ($i = 0; $i < $this->_packCnt; $i++) {
            $data = substr($data, 2);
            // 这里是因为设备数量包含了TC，所以实际比TA的个数多一个，我们这里记录的电池数量为TA的数量。
            $this->_cellNumList[$i] = $ret[$mpm->mp(DT_BT_CELL_NUM, $i)] = unpack('nv', $data)['v'] - 1;
        }

        return $ret;
    }

    private function parsePackData($msg, $mpName, $fmt, $dataSize) {
        $ret = array();
        $mpm = MeasurePointManager::instance();
        $data = $msg->data;
        for ($i = 0; $i < $this->_packCnt; $i++) {
            $mp = $mpm->mp($mpName, $i);
            if ($fmt == 'f') { // 浮点数格式和'f'格式不一样，'f'表示是ABCD，而上传的格式为BADC
                $ret[$mp] = unpack($fmt . 'v', $data[1] . $data[0] . $data[3] . $data[2])['v'];
            }
            else if ($fmt == 'N') { // 32位无符号整形也不一样，ABCD，'N'表示是ABCD，上传的格式为CDAB
                $ret[$mp] = unpack($fmt . 'v', $data[2] . $data[3] . $data[0] . $data[1])['v'];
            }
            else {
            $ret[$mp] = unpack($fmt . 'v', $data)['v'];
            }
            $data = substr($data, $dataSize);
        }

        return $ret;
    }

    private function parseConnectionBitData($msg) {
        $ret = array();
        $mpm = MeasurePointManager::instance();
        $data = $msg->data;
        $dataLen = strlen($data);
        Logger::info('[' . __METHOD__ . ']', array('data' => String::bin2Str($data), 'len' => $dataLen));
        $packIdx = 0;
        $cellIdx = 0;
        $stop = false;
        for ($i = $dataLen - 1; $i >= 0 && $stop == false; $i--) {
            $byteData = ord($data[$i]);
            $mask = 0x01;
            for ($bit = 0; $bit < 8 && $stop == false; $bit ++) {
                $bitValue = ($byteData & ($mask << $bit)) >> $bit;
                Logger::info('[' . __METHOD__ . ']', array('bitValue' => $bitValue, 'byteData' => $byteData, 'bit' => $bit, 'cellIdx' => $cellIdx, 'packIdx' => $packIdx));
                if ($cellIdx == $this->_cellNumList[$packIdx]) {
                    $mp = $mpm->mp(DT_BT_TC_CONNECTION, $packIdx);
                    $ret[$mp] = $bitValue;
                    $cellIdx = 0;
                    $packIdx += 1;
                    if ($packIdx >= $this->_packCnt) {
                        $stop = true;
                    }
                }
                else {
                    $mp = $mpm->mp(DT_BT_CELL_UNIT_TA_CONNECTION . '_' . ($cellIdx+1), $packIdx);
                    $ret[$mp] = $bitValue;
                    $cellIdx += 1;
                }
            }
        }
        return $ret;
    }

    private function parseCellData($msg, $mpName, $cnt, $fmt, $dataSize) {
        $ret = array();
        $mpm = MeasurePointManager::instance();
        $data = $msg->data;
        for ($i = 0; $i < $cnt; $i++) {
            $mp = $mpm->mp($mpName . '_' . ($i+1), $this->_currPack);
            if ($fmt == 'f') { // 浮点数格式和'f'格式不一样，'f'表示是ABCD，而上传的格式为BADC
                $ret[$mp] = unpack($fmt . 'v', $data[1] . $data[0] . $data[3] . $data[2])['v'];
            }
            else if ($fmt == 'N') { // 32位无符号整形也不一样，ABCD，'N'表示是ABCD，上传的格式为CDAB
                $ret[$mp] = unpack($fmt . 'v', $data[2] . $data[3] . $data[0] . $data[1])['v'];
            }
            else {
                $ret[$mp] = unpack($fmt . 'v', $data)['v'];
            }
            $data = substr($data, $dataSize);
        }

        return $ret;
    }

    private function saveData($data) {
        if (empty($data)) {
            return;
        }
        (new DataDao())->updateData($this->_sn, $data);
    }
}
