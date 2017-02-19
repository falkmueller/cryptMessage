<?php

namespace cryptMessage\entity;

use cryptMessage\lib\rsa;
use cryptMessage\lib\aes;

class message {

    public $body;
    public $header;
            
    function __construct(){
        $this->body = new messageBody();
        $this->header = new messageHeader();
    }
    
    public static function getFromRawRequest($rowMessage, $rsa_decrypt_key, &$error_message, $header_check = null){
        $rowMessage = json_decode(urldecode($rowMessage), true);
        
        if(!$rowMessage || !is_array($rowMessage) || !isset($rowMessage["header"])|| !isset($rowMessage["body"])|| !isset($rowMessage["key"])){
            $error_message = "incorrect rowMessage";
            return null;
        }

        //set Header
        $rsa_lib = new rsa();
        $header_key =  $rsa_lib->decrypt($rowMessage["key"], $rsa_decrypt_key);
        
        if(!$header_key){
            $error_message = "key can not be decrypt";
            return null;
        }
        
        $aes_lib = new aes(256);
        $header = json_decode($aes_lib->decrypt($rowMessage["header"], $header_key), true);
        if(!$header){
            $error_message = "header can not be decrypt";
            return null;
        }
        
        $message = new static();
        $message->header->fromArray($header);
        
        if($header_check && is_callable($header_check)){
            if(!$header_check($message->header)){
               $error_message = "invald header";
                return null; 
            }
        }
        
        //set body
        if(md5($rowMessage["body"].$message->header->slug) !== $message->header->digest){
            $error_message = "invald body digest";
            return null;
        }
        
        $body = json_decode($aes_lib->decrypt($rowMessage["body"], base64_decode($message->header->cryptKey)), true);
        if(!$body){
            $error_message = "body can not be decrypt";
            return null;
        }
        
        $message->body->fromArray($body);
        
        return $message;
    }

    public function getCryptMessage($rsa_encrypt_key){
        $aes_lib = new aes(256);
        
        $body_key = $aes_lib->generateKey();
        
        $rawBody = $aes_lib->encrypt(json_encode($this->body),$body_key);
        
        $this->header->cryptKey = base64_encode($body_key);
        $this->header->digest = md5($rawBody.$this->header->slug);
        
        $header_key = $aes_lib->generateKey();
        $rawHeader = $aes_lib->encrypt(json_encode($this->header), $header_key);
        
        $rsa_lib = new rsa();
        $rawKey = $rsa_lib->encrypt($header_key, $rsa_encrypt_key);
        
        if(!$rawBody || !$rawHeader || !$rawKey){
            return null;
        }
        
        return urlencode(json_encode(array("key" => $rawKey, "header" => $rawHeader, "body" => $rawBody)));
    }
    
}
