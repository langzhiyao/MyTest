<?php
include_once 'BaseRequest.php';
include_once 'Parameter.php';

class ExtractAudioTaskReq extends Parameter implements JsonSerializable{

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
        parent::validate();
        if (empty($this->getAssetId())){
            throw new VodException('VOD.100011001',"asset_id is invalidate!");
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