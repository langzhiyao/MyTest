<?php

namespace app\common\model;

use think\Model;

class Document extends Model {

    /**
     * 查询所有系统文章
     */
    public function getList() {
        return db('document')->select();
    }

    /**
     * 根据编号查询一条
     * 
     * @param unknown_type $id
     */
    public function getOneById($id) {
        return db('document')->where('doc_id',$id)->find();
    }

    /**
     * 根据标识码查询一条
     * 
     * @param unknown_type $id
     */
    public function getOneByCode($code) {
        return db('document')->where('doc_code',$code)->find();
    }

    /**
     * 更新
     * 
     * @param unknown_type $param
     */
    public function update1($param) {
        return db('document')->where('doc_id',$param['doc_id'])->update($param);
    }
}

?>
