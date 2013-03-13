<?php
/*
Sean Huber CURL library
This library is a basic implementation of CURL capabilities.
It works in most modern versions of IE and FF.
==================================== USAGE ====================================
It exports the CURL object globally, so set a callback with setCallback($func).
(Use setCallback(array('class_name', 'func_name')) to set a callback as a func
that lies within a different class)
Then use one of the CURL request methods:
get($url);
post($url, $vars); vars is a urlencoded string in query string format.
Your callback function will then be called with 1 argument, the response text.
If a callback is not defined, your request will return the response text.
*/
class CURL {
        
	var $callback = false;
        var $cookie_path = '';
        
        function CURL(){
                
                $this->makeCookiePath();        
        }
        
	function setCallback($func_name){
                
		$this->callback = $func_name;
	}
         
	function doRequest($method, $url, $vars, $referer = null, $headers = null, $cookie_path = null){
                
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                if($headers)
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                else
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Language: zh-cn,zh;q=0.5', 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', 'Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7', 'Connection: keep-alive'));

		if($referer)curl_setopt($ch, CURLOPT_REFERER, $referer);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:2.0.1) Gecko/20100101 Firefox/4.0.1');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                
                if(!$cookie_path){
                        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_path);
                        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_path);                
                }else{
                        $this->cookie_path = $cookie_path;
                        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_path);
                        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_path);                        
                }
                
		
		if ($method == 'POST') {
                        //if(is_array($vars)) $vars = http_build_query($vars);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
		} 
                
		$data = curl_exec($ch);
                
		if ($data) {
			curl_close($ch);
			if ($this->callback) {
				$callback = $this->callback;
				$this->callback = false;
				return call_user_func($callback, $data);
			} else {
				return $data;
			} 
		} else {
			$err = curl_error($ch);
			curl_close($ch);
			return $err;
		} 
	} 
                                                
	function get($url, $referer = null){
                
		return $this->doRequest('GET', $url, 'NULL', $referer);
	} 
        
	function post($url, $vars, $referer = null) {
                
		return $this->doRequest('POST', $url, $vars, $referer);
	}
        
        function makeCookiePath(){
                
                $prefix = md5(time());
                $this->cookie_path = '/tmp/curl_cookies/'.$prefix[0].$prefix[1];        
                mkdirs($this->cookie_path); 
        }
        
        function readcookies(){
                
                $result = '';
                $cookie_arrray = @file($this->cookie_path);
                if(!$cookie_arrray)return;
                foreach($cookie_arrray as $line){
                        $tmp = preg_split("/\t/", $line);
                        if(isset($tmp[5]))$result[trim($tmp[5])] = trim($tmp[6]);
                }
                return $result;
        }
        
        function clearcookies(){
                if(is_file($this->cookie_path))unlink($this->cookie_path);
        }
        
        function addcookies($new_cookies){
                
                $my_cookies = file_get_contents($this->cookie_path);
                
                $my_cookies = trim($my_cookies, "\n");
                
                if(is_array($new_cookies)){
                        
                        $new_cookies = implode("\n", $new_cookies);
                        $my_cookies = $my_cookies."\n".$new_cookies;
                }else{
                        $my_cookies = $my_cookies."\n".$new_cookies;
                }

                file_put_contents($this->cookie_path, $my_cookies);
        }
} 

?>
