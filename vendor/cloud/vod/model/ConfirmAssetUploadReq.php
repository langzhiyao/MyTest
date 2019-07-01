<?php
include_once 'BaseRequest.php';

class ConfirmAssetUploadReq extends BaseRequest{

    private $assetId;

    private $status;

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
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
        $this->serializedNamedParam['status'] = $status;
    }

    public function validate()
    {
        if (empty($this->assetId)){
            throw new VodException('VOD.100011001',"bucket is invalidate!");
        }
        if (empty($this->status)){
            throw new VodException('VOD.100011001',"bucket is invalidate!");
        }
    }
}