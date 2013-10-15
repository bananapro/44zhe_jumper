<?php

class RController extends AppController {

	var $name = 'R';
	var $uses = array('StatRedirect');
	var $tag = '44haitaoad1-20';

	function index(){

		$origin = $_GET['t'];
		$source = $_GET['c'];
		if(!$origin)die();
		if(!$source)$source = '1';

		$target_url = $origin;
		//此处以后扩展规则，目前仅有amazon首页
		if(trim($origin) == 'http://www.amazon.com'){
			$target_url = 'http://www.amazon.com/?_encoding=UTF8&camp=1789&creative=9325&linkCode=ur2&tag=44haitaoad1-20';

		}else if(preg_match('/amazon.com\/.+?\/dp/i', $origin) || preg_match('/amazon.com\/gp\/product/i', $origin)){
			//http://go.44zhe.com/r?c=1001&t=http%3a%2f%2fwww.amazon.com%2fgp%2fproduct%2fB00A17IAO0%3fget%3dmy

			$param = parse_url($origin);
			if(isset($param['query']))
				parse_str($param['query'], $param_query);
			else
				$param_query = array();
			$param_query['tag'] = $this->tag;
			$param['query'] = http_build_query($param_query);
			$target_url = 'http://' . $param['host'] . $param['path'] . '?' . $param['query'];
		}

		$target = urlencode(base64_encode($target_url));

		$this->StatRedirect->save(array('source'=>$source, 'ip'=>getip(), 'area'=>getAreaByIp(), 'client'=>getBrowser(), 'origin'=>$origin, 'target'=>$target_url));

		$this->redirect('http://haitao.44zhe.com/?l=go&r='.$target.'&c='.$source);

	}
}

?>