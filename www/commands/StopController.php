<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker:   */

/*******************************************************************
 * @File: StopController.php
 * $Id: StopController.php v 1.0 2016-04-12 14:20:27 maxing $
 * $Author: maxing xm.crazyboy@gmail.com $
 * $Last modified: 2016-04-12 15:10:53 $
 * @brief
 *
 ******************************************************************/

namespace app\commands;

use yii;
use yii\console\Controller;
use Workerman\Worker;

class StopController extends Controller {
    public function actionIndex() {
        Worker::runAll();
    }
}
