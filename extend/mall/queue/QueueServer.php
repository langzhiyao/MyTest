<?php

namespace mall\queue;

class QueueServer {
    
    private $_queuedb;
    
    public function __construct() {
        $this->_queuedb = new QueueDB();
    }

    /**
     * 取出队列
     * @param unknown $key
     */
    public function pop($key,$time) {
        return unserialize($this->_queuedb->pop($key,$time));
    }

    public function scan() {
        return $this->_queuedb->scan();
    }
}
?>
