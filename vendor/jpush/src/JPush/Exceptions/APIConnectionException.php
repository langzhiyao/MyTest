<?php
namespace JPush\Exceptions;

class APIConnectionException extends JPushException {
	public $code;
    public $message;
    
    function __toString() {
        return "\n" . __CLASS__ . " -- {$this->message} \n";
    }
}
