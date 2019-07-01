<?php
include_once 'BaseRequest.php';

class QueryCategoryReq extends BaseRequest {

    private $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
        $this->serializedNamedParam['id'] = $id;
    }


    public function validate()
    {
        if ($this->id < 0){
            throw new VodException('VOD.100011001',"id is invalidate!");
        }
    }
}