<?php

namespace cryptMessage\lib;

class rsa {
    
    public function generateKeys(&$private_Key, &$public_Key, $config= array()){
        
        $default_config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );
        
        $config = array_merge($default_config, $config);
        
        $res_private_key = @openssl_pkey_new($config);

        if(!$res_private_key){
            echo false;
        } else {
            openssl_pkey_export($res_private_key, $private_Key);
            $public_Key = openssl_pkey_get_details($res_private_key);
            $public_Key = $public_Key["key"];
            
            return true;
        }
    }

    public function encrypt($message, $private_key) {
        
        if(!openssl_private_encrypt($message, $krypt, $private_key)){
            return null;
        }
    
        return trim(helper::urlsafeB64Encode($krypt));
    }
    
    public function decrypt($krypt, $public_key) {
        
        if(!openssl_public_decrypt(helper::urlsafeB64Decode($krypt), $message, $public_key)){
            return null;
        } else {
            return trim($message);
        }
    }
    
    
    
}
