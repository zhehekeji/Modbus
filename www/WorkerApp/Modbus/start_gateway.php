<?php 
use \Workerman\Worker;
use \GatewayWorker\Gateway;
use \Workerman\Autoloader;
use app\Modbus\Protocol\Modbus;
use app\Modbus\Logger;

// 自动加载类
Autoloader::setRootPath(__DIR__);

if (!class_exists('\Protocols\Modbus')) {
    class_alias('app\Modbus\Protocol\Modbus', 'Protocols\Modbus');
}
$gateway = new Gateway("modbus://0.0.0.0:8284");
$gateway->name = 'NeizuModbusGateway';
$gateway->count = 1;
$gateway->lanIp = '127.0.0.1';
// 内部通讯起始端口，假如$gateway->count=4，起始端口为4000
// 则一般会使用4000 4001 4002 4003 4个端口作为内部通讯端口 
$gateway->startPort = 4200;
$gateway->registerAddress = '127.0.0.1:2440';

// 当客户端连接上来时
$gateway->onConnect = function($connection)
{
    Logger::info('new Connect from ' . $connection->getRemoteIp() . ':' . $connection->getRemotePort());
    //Logger::flush(true);
};

// 当客户端断开时
$gateway->onDisconnect = function($connection)
{
    Logger::info('client ' . $connection->getRemoteIp() . ':' . $connection->getRemotePort() . ' disconnect');
    //Logger::flush(true);
};

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}

