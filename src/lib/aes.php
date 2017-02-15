<?php

namespace cryptMessage\lib;

class aes {
  
  const M_CBC = 'cbc';
  const M_CFB = 'cfb';
  const M_ECB = 'ecb';
  const M_NOFB = 'nofb';
  const M_OFB = 'ofb';
  const M_STREAM = 'stream';
  
  protected $cipher;
  protected $mode;
  protected $blockSize;


  /**
* 
* @param type $data
* @param type $key
* @param type $blockSize
* @param type $mode
*/
  function __construct($blockSize = 256, $mode = null) {
    $this->setBlockSize($blockSize);
    $this->setMode($mode);
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

  
/**
* 
* @param type $blockSize
*/
  public function setBlockSize($blockSize) {
     $this->blockSize = $blockSize;
      
    switch ($blockSize) {
      case 128:
      $this->cipher = MCRYPT_RIJNDAEL_128;
      break;
      
      case 192:
      $this->cipher = MCRYPT_RIJNDAEL_192;
      break;
      
      case 256:
      $this->cipher = MCRYPT_RIJNDAEL_256;
      break;
    }
  }
  
/**
* 
* @param type $mode
*/
  public function setMode($mode) {
    switch ($mode) {
      case AES::M_CBC:
      $this->mode = MCRYPT_MODE_CBC;
      break;
      case AES::M_CFB:
      $this->mode = MCRYPT_MODE_CFB;
      break;
      case AES::M_ECB:
      $this->mode = MCRYPT_MODE_ECB;
      break;
      case AES::M_NOFB:
      $this->mode = MCRYPT_MODE_NOFB;
      break;
      case AES::M_OFB:
      $this->mode = MCRYPT_MODE_OFB;
      break;
      case AES::M_STREAM:
      $this->mode = MCRYPT_MODE_STREAM;
      break;
      default:
      $this->mode = MCRYPT_MODE_ECB;
      break;
    }
  }
  
/**
* 
* @return boolean
*/
  public function validateParams($data, $key) {
    if ($data == null){
       throw new \Exception('Invlid params: no data'); 
    }
    
    if ($key == null){
       throw new \Exception('Invlid params: no key'); 
    }
    
    if(strlen($key) * 8 != $this->blockSize){
        throw new \Exception('Invlid params: kay length'); 
    }
    
    if ($this->cipher == null){
       throw new \Exception('Invlid params: cipher'); 
    }
      
    
    return true;

  }

  protected function getIV() {
      return mcrypt_create_iv(mcrypt_get_iv_size($this->cipher, $this->mode), MCRYPT_RAND);
  }
  
/**
* @return type
* @throws Exception
*/
  public function encrypt($data, $key) {
    
    if ($this->validateParams($data, $key)) {
      return trim(helper::urlsafeB64Encode(
        mcrypt_encrypt(
          $this->cipher, $key, $data, $this->mode, $this->getIV())));
    } else {
      throw new \Exception('Invlid params!');
    }
  }
/**
* 
* @return type
* @throws Exception
*/
  public function decrypt($data, $key) {
    if ($this->validateParams($data, $key)) {
      return trim(mcrypt_decrypt(
        $this->cipher, $key, helper::urlsafeB64Decode($data), $this->mode, $this->getIV()));
    } else {
      throw new \Exception('Invlid params!');
    }
  }
  
}
