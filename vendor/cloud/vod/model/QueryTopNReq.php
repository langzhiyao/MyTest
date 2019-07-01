<?php

class QueryTopNReq{
    //加速域名，格式：www.test1.com。ALL表示查询名下全部域名（TopN视频信息要么查询单个域名要么查询所有域名）
    private $domain;

    //查询日期，格式为yyyymmdd
    private $date;

    /**
     * @return mixed
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param mixed $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    public function buildQueryArray()
    {
        $queryArray = [];
        if (!empty($this->domain)){
            $queryArray["domain"] = $this->domain;
        }
        if (!empty($this->date)){
            $queryArray["date"] = $this->date;
        }
        return $queryArray;
    }
}