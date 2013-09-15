<?php

class ProxyController extends AppController {

	var $name = 'Proxy';
	var $uses = array();
	var $proxy;

	function index(){
		require MYLIBS . 'tranProxy.class.php';
		$this->proxy = new tranProxy();

		echo $this->_get_cache_static($_SERVER["HTTP_HOST"] . $_GET['path']);die();
	}


	/**
	 * 缓存静态资源(css/js/img)
	 * @param type $url
	 * @return type
	 */
	function _get_cache_static($url){

		$md5 = md5($url);
		$path = "static".DS."{$md5[0]}".DS.$md5;
		$static_cache = cache($path, null, 86400);
		if(!$static_cache){
			$static_cache = $this->proxy->request($url);
			//去除UTF BOM头
			if (substr($static_cache, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf)) {
				$static_cache = substr($static_cache, 3);
			}
			$is_static = false;
			if(preg_match('/(.jpg|.gif|.bmp|.png|.js|.css|.ico)$/i', $url)){
				$_SESSION['shutdown_debug'] = true;
				$is_static = true;
			}

			if($static_cache && $is_static)cache($path, $this->proxy->response_headers . "{||}" . $static_cache);

		}else{

			//恢复缓存的头部
			list($this->proxy->response_headers, $static_cache) = explode("{||}", $static_cache);
			$this->proxy->return_response(true);
		}
		return $static_cache;
	}
}

?>