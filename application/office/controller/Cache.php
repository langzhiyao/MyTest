<?php

namespace app\office\controller;

use think\Lang;
use think\Validate;

class Cache extends AdminControl {

    function clear() {
        delCacheFile('temp');
        delCacheFile('cache');
        $this->success("操作完成!!!", url('office/Dashboard/index'));
        exit();
    }


}

?>
