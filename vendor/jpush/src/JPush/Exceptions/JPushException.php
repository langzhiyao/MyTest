<?php
namespace JPush\Exceptions;

class JPushException extends \Exception {
	public $code;
    public $message;
    
    function __construct($message) {
        parent::__construct($message);
    }
}
