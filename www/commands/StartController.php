<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker:   */

/*******************************************************************
 * @File: StartController.php
 * $Id: StartController.php v 1.0 2016-04-12 14:19:58 maxing $
 * $Author: maxing xm.crazyboy@gmail.com $
 * $Last modified: 2017-09-29 15:12:45 $
 * @brief
 *
 ******************************************************************/

namespace app\commands;

use yii;
use yii\console\Controller;
use Workerman\Worker;

class StartController extends Controller {
    public $deamon = false;

    public function actionIndex($d = '', $app = 'Monitor') {
        $files = dirname(__DIR__)."/vendor/workerman/*.pid";
        exec("rm $files");
        // 加载所有WorkerApp/start*.php，以便启动所有服务
        foreach(glob(dirname(__DIR__)."/WorkerApp/*/start*.php") as $start_file) {
            require_once $start_file;
        }
        Worker::$stdoutFile = Yii::$app->runtimePath. '/logs/' . date("Y_m_d").'.log';
        Worker::runAll();
        //Yii::$app->db->createCommand("set global wait_timeout=2592000")->execute();
    }

    public function optionAliases() {
        return [
            'd' => 'deamon',
        ];
    }

    public function options() {
        return ['deamon'];
    }
}
