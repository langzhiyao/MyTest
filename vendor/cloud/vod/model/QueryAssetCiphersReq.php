<?php
include_once 'BaseRequest.php';

class QueryAssetCiphersReq extends BaseRequest {

    private $assetId;

    /**
     * @return mixed
     */
    public function getAssetId()
    {
        return $this->assetId;
    }

    /**
     * @param $assetId
     */
    public function setAssetId($assetId)
    {
        $this->assetId = $assetId;
        $this->serializedNamedParam['asset_id'] = $assetId;
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
}