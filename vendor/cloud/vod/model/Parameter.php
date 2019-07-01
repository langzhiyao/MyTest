<?php
include_once 'BaseRequest.php';
class Parameter extends BaseRequest implements JsonSerializable{

    private $format;

    /**
     * @return mixed
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param mixed $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
        $this->serializedNamedParam["format"] = $format;
    }

    public function validate()
    {
        if (empty($this->getFormat())){
            throw new VodException('VOD.100011001',"format is invalidate!");
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