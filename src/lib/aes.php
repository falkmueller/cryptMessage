<?php

namespace cryptMessage\lib;

class aes {

  protected $blockSize;


/**
* 
* @param type $blockSize
*/
  function __construct($blockSize = 256) {
    $this->blockSize = $blockSize;
  }
  
  public function generateKey(){
        $characters = "1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ,;.:-_#+*~!\"§$%&/()=?\\][}{|<>";
        $length = intval($this->blockSize / 8);
      
        $random = "";
        for ($i = 0; $i < $length; $i ++) {
            $pos = (microtime(true) + mt_rand(10000,90000)) % strlen($characters);
            $random .= @$characters[$pos];
        }

        return $random;
  }
  
  public function encrypt($data, $password) {  
      
        $salt = openssl_random_pseudo_bytes(8);

        $salted = '';
        $dx = '';
        
        while (strlen($salted) < 48) {
          $dx = md5($dx.$password.$salt, true);
          $salted .= $dx;
        }

        $key = substr($salted, 0, 32);
        $iv  = substr($salted, 32,16);

        $encrypted_data = openssl_encrypt($data, 'aes-'.$this->blockSize.'-cbc', $key, true, $iv);
        return base64_encode('Salted__' . $salt . $encrypted_data);
    }
    
    public function decrypt($edata, $password) {
        $data = base64_decode($edata);
        $salt = substr($data, 8, 8);
        $ct = substr($data, 16);
        
        $salted = '';
        $dx = '';
        
        while (strlen($salted) < 48) {
          $dx = md5($dx.$password.$salt, true);
          $salted .= $dx;
        }

        $key = substr($salted, 0, 32);
        $iv  = substr($salted, 32,16);
        
        return openssl_decrypt($ct, 'aes-'.$this->blockSize.'-cbc', $key, true, $iv);
        
    }
  
}
