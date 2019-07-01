<?php
abstract class BaseRequest
{
    public abstract function validate();

    protected  $serializedNamedParam = array();

    /**
     * @return array
     */
    public function getSerializedNamedParam(): array
    {
        return $this->serializedNamedParam;
    }

    /**
     * @param array $serializedNamedParam
     */
    public function setSerializedNamedParam(array $serializedNamedParam)
    {
        $this->serializedNamedParam = $serializedNamedParam;
    }
}