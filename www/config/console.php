<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['trace', 'error', 'warning', 'info'],
                    'categories' => ['modbus'],
                    'logFile' => '@app/runtime/logs/modbus.log',
                    'logVars' => [],
                    'exportInterval' => 100,
                    'maxLogFiles' => 11,
                    'maxFileSize' => 40960, // in KB
                ],
            ],
        ],
        'redis' => $params['redis'],
    ],
    'params' => $params,
];

return $config;
