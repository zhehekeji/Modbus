<?php 
use \Workerman\Worker;
use \GatewayWorker\Register;

$register = new Register('text://0.0.0.0:2440');
$register->name = 'NeizuModbusRegister';

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}

