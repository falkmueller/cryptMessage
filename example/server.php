<?php

require_once '../standAloneLoader.php';

header('Content-type: application/json');
$result = array();


if($_REQUEST["func"] == "generateKeys"){
    
    $rsa_lib = new cryptMessage\lib\rsa();
    
    $rsa_lib->generateKeys($private_Key, $public_Key);
    $result["private_key_1"] = $private_Key;
    $result["public_key_1"] = $public_Key;
    
    $rsa_lib->generateKeys($private_Key, $public_Key);
    $result["private_key_2"] = $private_Key;
    $result["public_key_2"] = $public_Key;
}
elseif ($_REQUEST["func"] == "generateCryptMessage") {
    $message = new cryptMessage\entity\message();
    $message->body->data = $_REQUEST["data"];
    
    $result["crypt_message"] = $message->getCryptMessage($_REQUEST["private_key"]);
    if(empty($result["crypt_message"])){
        $result["message"] = "massage con not be create.";
    }  
}
elseif ($_REQUEST["func"] == "decryptMessage"){
    
    $cryptMessage = $_REQUEST["cryptMessage"];
    $public_key = $_REQUEST["public_key"];
    $error_message = null;
    $header_check = function($header){ return true;/*check timestamp und if slug unique*/};
    $message = cryptMessage\entity\message::getFromRawRequest($cryptMessage, $public_key, $error_message);
    
    if($error_message){
        $result["message"] = $error_message;
    } else {
        $result["decrypt_message"] = json_encode($message);
        $result["decrypt_data"] = $message->body->data;
    }
}

echo json_encode( $result );