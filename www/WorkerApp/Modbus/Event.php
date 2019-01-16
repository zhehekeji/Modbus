<?php
/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use app\Modbus\Client\ClientManager;
use app\Modbus\Logger;

/**
 * 主要是处理 onConnect onMessage onClose 三个方法
 */
class Event {
    public static function onConnect($clientId) {
        ClientManager::instance()->onClientConnect($clientId);
    }

   /**
    * @param int $clientId 连接id
    * @param mixed $message 具体消息
    */
    public static function onMessage($clientId, $msg) {
        ClientManager::instance()->onMessage($clientId, $msg);
        Logger::flush(true);
   }

   /**
    * @param int $clientId 连接id
    */
   public static function onClose($clientId) {
        ClientManager::instance()->onClientClose($clientId);
   }
}
