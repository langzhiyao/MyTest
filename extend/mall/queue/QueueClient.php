<?php
namespace mall\queue;
use app\common\logic\Queue;

class QueueClient {

    private static $queuedb;

    /**
     * 入列
     * @param string $key
     * @param array $value
     */
    public static function push($key, $value) {
        if (!config('queue.open')) {
            $QueueLogic = new Queue();
            $QueueLogic->$key($value);return;
//            Logic('queue')->$key($value);return;
        }
        if (!is_object(self::$queuedb)) {
            self::$queuedb = new QueueDB();
        }
        return self::$queuedb->push(serialize(array($key=>$value)));
    }
}

?>
