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

    public function encrypt($message, $key, $is_public = true) {
        
        if(!$is_public && !openssl_private_encrypt($message, $krypt, $key)){
            return null;
        }
        elseif ($is_public && !openssl_public_encrypt($message, $krypt, $key)){
            return null;
        }
    
        return trim(base64_encode($krypt));
    }
    
    public function decrypt($krypt, $key, $is_private = true) {
        
        if(!$is_private && !openssl_public_decrypt(base64_decode($krypt), $message, $key)){
            return null;
        }
        elseif ($is_private && !openssl_private_decrypt(base64_decode($krypt), $message, $key)){
            return null;
        }
            
        return trim($message);
      
    }
    
    
    
}
