<?php

include_once 'BaseRequest.php';

class AssetProcessReq extends BaseRequest implements JsonSerializable {

    private $assetId;

    private $transPresetId;

    private $watermarkTemplateId;

    private $templateGroupName;

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
    public function getTransPresetId()
    {
        return $this->transPresetId;
    }

    /**
     * @param mixed $transPresetId
     */
    public function setTransPresetId($transPresetId)
    {
        $this->transPresetId = $transPresetId;
        $this->serializedNamedParam['trans_template_id'] = $transPresetId;
    }

    /**
     * @return mixed
     */
    public function getWatermarkTemplateId()
    {
        return $this->watermarkTemplateId;
    }

    /**
     * @param mixed $watermarkTemplateId
     */
    public function setWatermarkTemplateId($watermarkTemplateId)
    {
        $this->watermarkTemplateId = $watermarkTemplateId;
        $this->serializedNamedParam['watermark_template_id'] = $watermarkTemplateId;
    }

    /**
     * @return mixed
     */
    public function getTemplateGroupName()
    {
        return $this->templateGroupName;
    }

    /**
     * @param mixed $templateGroupName
     */
    public function setTemplateGroupName($templateGroupName)
    {
        $this->templateGroupName = $templateGroupName;
        $this->serializedNamedParam['template_group_name'] = $templateGroupName;
    }
    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;
        $this->serializedNamedParam['thumbnail'] = $thumbnail;
    }

    public function jsonSerialize()
    {
        $data = [];
        foreach ($this->getSerializedNamedParam() as $key=>$val){
            if ($val !== null) $data[$key] = $val;
        }
        return $data;
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