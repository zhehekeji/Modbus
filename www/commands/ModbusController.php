<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker:   */

/*******************************************************************
 * @File: ModbusController.php
 * $Id: ModbusController.php v 1.0 2018-07-31 13:05:27 maxing $
 * $Author: maxing xm.crazyboy@gmail.com $
 * $Last modified: 2018-08-20 09:46:06 $
 * @brief
 *
 ******************************************************************/

namespace app\commands;

use yii;
use yii\console\Controller;
use app\Modbus\Protocol\Message;
use app\Modbus\Client\Client;
use app\utility\String;

class ModbusController extends BaseController {
    public $ip = '127.0.0.1';
    public $port = 8586;
    public $sn   = '0123456789ab';
    public $packCnt = 2; // 一共两组
    public $cellCnt = 4; // 每组4节电池
    public $packCurr = array(100.0, 99.9);
    public $packTemp1 = array(25.3, 25.2);
    public $packTemp2 = array(25.1, 25.5);
    public $packTcStatus = array(0, 1);
    public $packAh = array(500, 450);
    public $packCurrFac = array(1, 2);
    public $packConnection = array(
        0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00,
        0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00,
        0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00,
        0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00,
        0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00,
        0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00,
        0x00, 0x00, 0x00, 0x11
    );
    public $cellVolt = array(12.2, 12.52, 12.34, 12.22);
    public $cellTemp = array(25.2, 25.1, 25.0, 25.3);
    public $cellRes = array(1000, 2000, 3000, 4000);
    public $cellTempAlarm = array(0, 4, 0, 0);
    public $cellResAlarm = array(0, 4, 8, 0);
    public $cellTaStatus = array(0, 1, 0, 0);

    public function actionIndex() {
        $socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
        socket_connect($socket, $this->ip, $this->port);
        while (true) {
            $data = socket_read($socket, 8192);
            echo 'Receive:' . String::bin2str($data) . "\n";
            $msg = new Message;
            $msg->decode($data);
            switch ($msg->func) {
            case Message::FUNC_INFO:
                $this->sendSn($socket, $msg);
                break;
            case Message::FUNC_MODBUS_READ:
                switch ($msg->tagH) {
                case Client::STEP_INFO:
                    $this->sendDeviceInfo($socket, $msg);
                    break;
                case Client::STEP_PACK_CURR:
                    $this->sendData($socket, $msg, $this->packCurr, 'f', 4);
                    break;
                case Client::STEP_PACK_TEMP_1:
                    $this->sendData($socket, $msg, $this->packTemp1, 'f', 4);
                    break;
                case Client::STEP_PACK_TEMP_2:
                    $this->sendData($socket, $msg, $this->packTemp2, 'f', 4);
                    break;
                case Client::STEP_PACK_TC_STATUS:
                    $this->sendData($socket, $msg, $this->packTcStatus, 'n', 2);
                    break;
                case Client::STEP_PACK_AH:
                    $this->sendData($socket, $msg, $this->packAh, 'f', 4);
                    break;
                case Client::STEP_PACK_CURR_FAC:
                    $this->sendData($socket, $msg, $this->packTemp1, 'f', 4);
                    break;
                case Client::STEP_PACK_CONNECTION:
                    $this->sendBitData($socket, $msg, $this->packConnection);
                    break;
                case Client::STEP_CELL_VOLT:
                    $this->sendData($socket, $msg, $this->cellVolt, 'f', 4);
                    break;
                case Client::STEP_CELL_TEMP:
                    $this->sendData($socket, $msg, $this->cellTemp, 'f', 4);
                    break;
                case Client::STEP_CELL_RES:
                    $this->sendData($socket, $msg, $this->cellRes, 'N', 4);
                    break;
                case Client::STEP_CELL_TEMP_ALARM:
                    $this->sendData($socket, $msg, $this->cellTempAlarm, 'n', 2);
                    break;
                case Client::STEP_CELL_RES_ALARM:
                    $this->sendData($socket, $msg, $this->cellResAlarm, 'n', 2);
                    break;
                case Client::STEP_CELL_TA_STATUS:
                    $this->sendData($socket, $msg, $this->cellTaStatus, 'n', 2);
                    break;
                }
            }
        }
    }

    private function sendSn($socket, $oldMsg) {
        $msg = new Message;
        $msg->isRequest = false;
        $msg->tagH = $oldMsg->tagH;
        $msg->tagL = $oldMsg->tagL;
        $msg->func = Message::FUNC_INFO;
        $msg->sn   = $this->sn;
        $this->send($socket, $msg);
    }

    private function sendDeviceInfo($socket, $oldMsg) {
        $msg = new Message;
        $msg->isRequest = false;
        $msg->tagH = $oldMsg->tagH;
        $msg->tagL = $oldMsg->tagL;
        $msg->func = Message::FUNC_MODBUS_READ;
        $msg->dataLen = 6;
        $msg->data = pack('n', $this->packCnt) . pack('n', $this->cellCnt+1) . pack('n', $this->cellCnt+1);
        $this->send($socket, $msg);
    }

    private function sendBitData($socket, $oldMsg, $data) {
        $msg = new Message;
        $msg->isRequest = false;
        $msg->tagH = $oldMsg->tagH;
        $msg->tagL = $oldMsg->tagL;
        $msg->func = Message::FUNC_MODBUS_READ;
        $msg->dataLen = count($data);
        $msg->data = '';
        foreach ($data as $item) {
            $msg->data .= chr($item);
        }
        $this->send($socket, $msg);
    }

    private function sendData($socket, $oldMsg, $data, $fmt, $dataSize) {
        $msg = new Message;
        $msg->isRequest = false;
        $msg->tagH = $oldMsg->tagH;
        $msg->tagL = $oldMsg->tagL;
        $msg->func = Message::FUNC_MODBUS_READ;
        $msg->dataLen = $dataSize * count($data);
        $msg->data = '';
        foreach ($data as $item) {
            if ($fmt == 'f') {
                $tmp = pack($fmt, $item);
                $msg->data .= $tmp[1] . $tmp[0] . $tmp[3] . $tmp[2];
            }
            else if ($fmt == 'N') {
                $tmp = pack($fmt, $item);
                $msg->data .= $tmp[2] . $tmp[3] . $tmp[0] . $tmp[1];
            }
            else {
                $msg->data .= pack($fmt, $item);
            }
        }
        $this->send($socket, $msg);
    }

    private function send($socket, $msg) {
        $data = $msg->encode();
        echo 'Send:' . String::bin2str($data) . "\n";
        socket_write($socket, $data, strlen($data));
    }
}
