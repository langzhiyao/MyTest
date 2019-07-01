<?php

class QueryAssetMetaReq{
    /**
     * 媒资id。
     */
    private $assetId;

    /**
     * 媒资状态，取值如下
    “CREATING”	   //创建中
    “UNPUBLISHED”  //未发布
    “PUBLISHED”    //已发布
    “FAILED”       //创建失败
    “DELETED”      //已经删除
     */
    private $status;

    /**
     * 起始时间
     * 格式为yyyymmddhhmmss。必须是与时区无关的UTC时间
     * 指定task_id时该参数无效
     */
    private $startTime;


    /**
     * 结束时间
     * 格式为yyyymmddhhmmss。必须是与时区无关的UTC时间
     * 指定task_id时该参数无效
     */
    private $endTime;


    /**
     * 分类ID
     */
    private $categoryId = PHP_INT_MAX;


    /**
     * 视频标签，单个标签不超过16个字节，最多不超过16个标签。多个用逗号分隔，UTF8编码
     */
    private $tags;

    /**
     * 在媒资标题、描述中模糊查询的字符串。POC版本暂不支持模糊查询。
     */
    private $queryString;


    /**
     * 分页编号。默认为0,指定asset_id时该参数无效
     */
    private $page = 0;


    /**
     * 每页记录数。默认10，范围[1,100],指定asset_id时该参数无效
     */
    private $size = 10;

    /**
     * @return mixed
     */
    public function getAssetId()
    {
        return $this->assetId;
    }

    /**
     * @param mixed $assetId
     */
    public function setAssetId($assetId)
    {
        $this->assetId = $assetId;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @param mixed $startTime
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    /**
     * @return mixed
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @param mixed $endTime
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }

    /**
     * @return mixed
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @param mixed $categoryId
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
    }

    /**
     * @return mixed
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param mixed $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return mixed
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * @param mixed $queryString
     */
    public function setQueryString($queryString)
    {
        $this->queryString = $queryString;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param mixed $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param mixed $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    public function buildQueryArray(){

        $queryArray = [];
        if (!empty($this->assetId)){
            $queryArray['asset_id'] = $this->assetId;
        }

        if (!empty($this->status)){
            $queryArray['status'] = $this->status;
        }

        if(!empty($this->startTime))
        {
            $queryArray['start_time'] = $this->startTime;
        }

        if(!empty($this->endTime))
        {
            $queryArray["end_time"] = $this->endTime;
        }

        if($this->categoryId != PHP_INT_MAX)
        {
            $queryArray["category_id"] = $this->categoryId;
        }

        if(!empty($this->tags))
        {
            $queryArray["tags"] = $this->tags;
        }

        if(!empty($this->queryString))
        {
            $queryArray["query_string"] = $this->queryString;
        }

        if(!empty($this->page) || $this->page != 0)
        {
            $queryArray["page"] = $this->page;
        }

        if(!empty($this->size) || $this->size != 10)
        {
            $queryArray["size"] = $this->size;
        }
        return $queryArray;

    }

}