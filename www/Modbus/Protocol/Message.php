<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker:   */

/*******************************************************************
 * @File: Message.php
 * $Id: Message.php v 1.0 2018-07-25 15:03:02 maxing $
 * $Author: maxing xm.crazyboy@gmail.com $
 * $Last modified: 2018-07-31 15:14:50 $
 * @brief
 *
 ******************************************************************/

namespace app\Modbus\Protocol;

use app\Modbus\Logger;
use app\utility\String;

class Message {
    // 协议中不区分发送还是请求，用标志位来区分
    public $isRequest = true;

    // 通讯包头
    public $tagH = 0;       // 传输标志H，1字节，发起端产生，应答时复制
    public $tagL = 0;       // 传输标志L，1字节，发起端产生，应答时复制
    public $protocol = 0;   // 协议标志，2字节，0表示Modbus，其他未定义
    public $lenth;          // 除通讯包头以外的数据包长度，也就是整包长度-6

    public $siteNum = 0x7D; // 站号
    public $func;           // 功能码
    // PLC控制协议字段
    public $subFunc = 0x80; // 子功能码
    public $sn;             // 设备id

    // modbus协议字段
    public $startAddr;      // modbus起始地址
    public $addrLen;        // modbus读取的地址长度，modbus按字算长度，一个地址占2个字节。
    public $dataLen;        // 应答的数据总长度（字节数）
    public $data;           // 应答的数据

    const FUNC_INFO             = 0x69;
    const FUNC_MODBUS_READ      = 0x03;
    const FUNC_MODBUS_WRITE     = 0x06;
    const FUNC_MODBUS_WRITEM    = 0x10;

    public function __construct() {
    }

    public function decode($buffer) {
        $this->tagH = ord($buffer[0]);
        $this->tagL = ord($buffer[1]);
        $this->protocol = unpack('np', substr($buffer, 2))['p'];
        $this->lenth = unpack('nl', substr($buffer, 4))['l'];
        $this->siteNum = ord($buffer[6]);
        $this->func = ord($buffer[7]);

        switch ($this->func) {
        case Message::FUNC_INFO:
            $this->subFunc = ord($buffer[8]);
            if ($this->isRequest == false) {
                $this->sn = substr($buffer, 9);
            }
            break;
        case Message::FUNC_MODBUS_READ:
            if ($this->isRequest) {
                $this->startAddr = unpack('ns', substr($buffer, 8))['s'];
                $this->addrLen   = unpack('nl', substr($buffer, 10))['l'];
            }
            else {
                $this->dataLen = ord($buffer[8]);
                $this->data    = substr($buffer, 9);
            }
            break;
        case Message::FUNC_MODBUS_WRITE:
            $this->startAddr = unpack('ns', substr($buffer, 8))['s'];
            $this->data = substr($buffer, 10);;
            break;
        }
    }

    public function encode() {
        $buffer  = '';
        $buffer .= chr($this->tagH);
        $buffer .= chr($this->tagL);
        $buffer .= pack('n', $this->protocol);

        $body    = '';
        $body   .= chr($this->siteNum);
        $body   .= chr($this->func);
        switch ($this->func) {
        case Message::FUNC_INFO:
            $body     .= chr($this->subFunc);
            if ($this->isRequest == false) {
                $body .= $this->sn;
            }
            break;
        case Message::FUNC_MODBUS_READ:
            if ($this->isRequest) {
                $body .= pack('n', $this->startAddr);
                $body .= pack('n', $this->addrLen);
            }
            else {
                $body .= chr($this->dataLen);
                $body .= $this->data;
            }
            break;
        case Message::FUNC_MODBUS_WRITE:
            $body     .= pack('n', $this->startAddr);
            $body     .= $this->data;
            break;
        }

        $buffer .= pack('n', strlen($body));
        $buffer .= $body;
        return $buffer;
    }
}
