<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker:   */

/*******************************************************************
 * @File: ClientManager.php
 * $Id: ClientManager.php v 1.0 2017-07-19 12:08:36 maxing $
 * $Author: maxing xm.crazyboy@gmail.com $
 * $Last modified: 2018-08-06 11:37:39 $
 * @brief
 *
 ******************************************************************/

namespace app\Modbus\Client;

use app\GatewayClient\Gateway;
use app\Modbus\Logger;
use app\utility\Singleton;
use app\utility\String;
use app\utility\Timer;

class ClientManager extends Singleton {
    protected static $_instance = null;
    private $_clientList = array();

    protected function init() {
        Timer::add(array($this, 'clientLiveCheck'), array(), 60, true);
    }

    public function clientLiveCheck() {
        foreach ($this->_clientList as $clientId => $client) {
            $time = $client->updateTime();
            // 10分钟没数据，认为连接超时
            if ($time == -1 || (time() - strtotime($time) > 600)) {
            }
        }
    }

    public function onClientConnect($clientId) {
        $client = new Client($clientId);
        $this->_clientList[$clientId] = $client;
        Logger::info('[' . __METHOD__ . ']', array('clientId' => $clientId));
    }

    public function onClientClose($clientId) {
        $client = array_value($this->_clientList, $clientId, null);
        if ($client != null) {
            $client->clearTimer();
        }
        unset($this->_clientList[$clientId]);
        Logger::info('[' . __METHOD__ . ']', array('clientId' => $clientId));
    }

    public function onMessage($clientId, $msg) {
        if (isset($this->_clientList[$clientId]) == false) {
            Logger::warning('[' . __METHOD__ . ']Client not connect', array('clientId' => $clientId));
            return;
        }
        $client = $this->_clientList[$clientId];
        $client->handleMessage($msg);
    }

    public function sendMessage($clientId, $msg) {
        //Logger::info('[' . __METHOD__ . ']', array('clientId' => $clientId));
        return Gateway::sendToClient($clientId, $msg);
    }

    public function closeClient($clientId) {
        return Gateway::closeClient($clientId);
    }
}
