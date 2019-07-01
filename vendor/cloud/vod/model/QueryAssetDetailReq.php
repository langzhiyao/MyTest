<?php
include_once 'BaseRequest.php';

class QueryAssetDetailReq extends BaseRequest implements JsonSerializable {

    private $assetId;

    private $categories;

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
        $this->serializedNamedParam["asset_id"] = $assetId;
    }

    /**
     * @return mixed
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param mixed $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
        $this->serializedNamedParam["categories"] = $categories;
    }



    /**
     * @throws VodException
     */
    public function validate()
    {
        if (empty($this->getAssetId())){
            throw new VodException('VOD.100011001',"asset_id is invalidate!");
        }
    }

    public function jsonSerialize()
    {
        $data = [];
        foreach ($this->serializedNamedParam as $key=>$val){
            if ($val !== null) $data[$key] = $val;
        }
        return $data;
    }
}