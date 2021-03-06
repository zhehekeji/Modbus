<?php 
use \Workerman\Worker;
use \GatewayWorker\BusinessWorker;
use \Workerman\Autoloader;

// 自动加载类
Autoloader::setRootPath(__DIR__);


// bussinessWorker 进程
$worker = new BusinessWorker();
// worker名称
$worker->name = 'NeizuModbusWorker';
// bussinessWorker进程数量
$worker->count = 1;
// 服务注册地址
$worker->registerAddress = '127.0.0.1:2440';

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}

