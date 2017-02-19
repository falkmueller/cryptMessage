<?php

namespace cryptMessage\entity;

class messageHeader {
    
    public $timestamp;
    public $slug;
    public $requestSlug;
    public $digest;
    public $action;
    public $cryptKey;
            
    function __construct(){
        $this->timestamp = time();
        $this->slug = uniqid("", true);
    }
    
    public function fromArray($array){
        $this->timestamp = isset($array["timestamp"]) ? $array["timestamp"]: 0;
        $this->slug = isset($array["slug"]) ? $array["slug"]: "";
        $this->requestSlug = isset($array["requestSlug"]) ? $array["requestSlug"]: "";
        $this->cryptKey = isset($array["cryptKey"]) ? $array["cryptKey"]: "";
        $this->digest = isset($array["digest"]) ? $array["digest"]: "";
        $this->action = isset($array["action"]) ? $array["action"]: "";
    }
    
}
