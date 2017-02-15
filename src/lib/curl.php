<?php

namespace cryptMessage\lib;

class curl {
    
    public static function exec($params){
    
        $defaults = array("url" => "", "method" => 'GET', 'postfields' => array(), 'cookies' => array());
        $params = array_merge($defaults, $params);

        $ch = curl_init($params["url"]);
        $headers = array();

        if(count($params["cookies"]) > 0){
            $cookie_line = '';
            foreach ($params["cookies"] as $name => $value){
                if ($cookie_line){$cookie_line .= "; ";}
                $cookie_line .= $name.'='.$value;
            }

            $headers[] = 'Cookie: '.$cookie_line;
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if($params["method"] == 'POST'){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $params["postfields"]);
            if(is_string($params["postfields"])){
                $headers[] = array('Content-Type: text/plain');
            }
        }

        $response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        curl_close($ch);

        //Split into lines
        $headers = explode("\n", $header);
        $cookie_lines = preg_grep('/^Set-Cookie:/', $headers);
        $cookies = array();
        foreach ($cookie_lines as $cookie_line){
            $cookie_line = current(explode(";", substr($cookie_line, strlen("Set-Cookie: "))));
            $cookie_name = substr($cookie_line, 0, strpos($cookie_line, "="));
            $cookies[$cookie_name] = substr($cookie_line, strpos($cookie_line, "=") + 1);
        }

        return array(
            "body" => $body,
            "header" => $headers,
            "cookie" => $cookies
        );
    }
    
}
