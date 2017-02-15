PHP cryptMessage
======

[![Author](https://img.shields.io/badge/author-falkm-blue.svg?style=flat-square)](https://falk-m.de)
[![Source Code](http://img.shields.io/badge/source-falkmueller/jSuggest-blue.svg?style=flat-square)](https://github.com/falkmueller/crypMessage)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Website](https://img.shields.io/website-falk-falk/http/falk-m.de.svg)](https://falk-m.de)

PHP repository for secure message exchange between applications
[Documentation and Demo](http://code.falk-m.de/crypeMessage/).

# install

cryptMessage is available via Composer:
```json
{
    "require": {
        "falkm/crypt-message": "1.*"
    }
}
```

Or download and include the outoloader
```php
<?php
    require_once 'cryptMessage/standAloneLoader.php';
```


# example usage

##1. generate RSA keys and change them on a secure way

```php
$rsa_lib = new cryptMessage\lib\rsa();<br/>
$private_Key = null; $public_Key = null;<br/>
$rsa_lib->generateKeys($clinet1_public_key, $clinet1_private_key);
$rsa_lib->generateKeys($clinet2_public_key, $clinet2_private_key);
```

Client 1 get $clinet1_private_key and $clinet2_public_key.
Client 2 get $clinet2_private_key and $clinet1_public_key.

##2. Client 1 create message for client 2

```php
use cryptMessage\entity\message; 

$message = new message();
$message->body->data = "test";
$crypt_message = $message->getCryptMessage($clinet2_public_key);
```

##3. client 1 transfer message to client 2

```php
$cookies = array(); /*if you receive cookies use them*/
$url = ''; /*Url of client 2 Api endpoint*/

$response = cryptMessage\lib\curl::exec(array(
    "url" => $url, 
    "method" => 'POST',
    "postfields" => $crypt_message,
    "cookies" => $cookies
));
```

## 4. client 2 receive message

```php
use cryptMessage\entity\message;

$error_message = null;
$request_body = file_get_contents("php://input");

$headercheck = function($header){
    /*if message timestamt older then 10min, then not accept message*/
    if($header->timestamp < (time() - 600)){
        return false;
    }

    /*delay attack protection
     message slug must be unique in last 10 minutes, check it over mySql table for examle*/
    checkUniqueMesssage($header->slug);
}

$request_message = message::getFromRawRequest($request_body, $clinet2_private_key, $error_message, $headercheck));
if($error_message || !$request_message || !($request_message instanceof message)){
    echo "ERROR"; /*message is not create with yout public key*/ 
    exit();
}

$response_message = new message();
$response_message->header->requestSlug = $request_message->header->slug;
$response_message->body->data = "hello, i receive: ".$request_message->body->data;
$rawResonse = $response_message->getCryptMessage($clinet1_public_key);

if(!$rawResonse){
    return "ERROR"; /*public key of client 1 is wrong*/
    exit();
}

echo $rawResonse;

```

##5. Client 1 receice the response from client 1

```php
/* $response is a array from culr request from step 3*/

if(!$response["body"] || $response["body"] == "ERROR"){
    echo "ERROR";
    exit();
 }
        
$request_slug = $request_message->header->slug;
$header_check = function($header) use ($request_slug){
    /*if message timestamt older then 10min, then not accept message*/
    if($header->timestamp < (time() - 600)){
        return false;
    }

    /*other client set slug of your message in his response as requestSlug*/
    if($header->requestSlug !== $request_slug){
        return false;
    }

    return true;
};
        
$response_message = message::getFromRawRequest($response["body"], $clinet1_private_key, $error_message, $header_check);
if($error_message || !$response_message || !($response_message instanceof message)){
    echo "ERROR"; /*resonse is wrong, detail in $error_message*/
    exit();
}

/*optional: cahce cookies for next api call*/
$cookies $response["cookie"];
    
echo $response_message->body->data;    

```