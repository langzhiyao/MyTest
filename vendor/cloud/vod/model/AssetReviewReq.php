<?php
include_once 'BaseRequest.php';

class AssetReviewReq extends BaseRequest implements JsonSerializable{

    private $assetId;

    private $review;

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
        $this->serializedNamedParam['asset_id'] = $assetId;
    }

    /**
     * @return mixed
     */
    public function getReview()
    {
        return $this->review;
    }

    /**
     * @param mixed $review
     */
    public function setReview($review)
    {
        $this->review = $review;
        $this->serializedNamedParam['review'] = $review;
    }

    public function validate()
    {
        if (empty($this->assetId)){
            throw new VodException('VOD.100011001',"asset_id is invalidate!");
        }
        if (empty($this->review)){
            throw new VodException('VOD.100011001',"review is invalidate!");
        }
    }


    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();
        foreach ($this->serializedNamedParam as $key=>$val){
            if ($val !== null) $data[$key] = $val;
        }
        return $data;
    }
}