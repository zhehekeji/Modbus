<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker:   */

/*******************************************************************
 * @File: StatusController.php
 * $Id: StatusController.php v 1.0 2016-04-12 14:20:06 maxing $
 * $Author: maxing xm.crazyboy@gmail.com $
 * $Last modified: 2016-04-12 15:10:46 $
 * @brief
 *
 ******************************************************************/

namespace app\commands;

use yii;
use yii\console\Controller;
use Workerman\Worker;

class StatusController extends Controller {
    public function actionIndex() {
        Worker::runAll();
    }
}
