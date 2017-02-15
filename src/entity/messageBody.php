<?php

namespace cryptMessage\entity;

class messageBody {
    
    public $success;
    public $data;
    public $message;
    public $message_key;
    
    function __construct(){
        $this->success = true;
    }


    public function fromArray($array){
        $this->success = isset($array["success"]) ? $array["success"]: false;
        $this->data = isset($array["data"]) ? $array["data"]: null;
        $this->message = isset($array["message"]) ? $array["message"]: null;
        $this->message_key = isset($array["message_key"]) ? $array["message_key"]: null;
    }
    
}
