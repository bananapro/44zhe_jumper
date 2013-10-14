<?php

class RController extends AppController {

	var $name = 'R';
	var $uses = array('StatRedirect');

	function index(){

		$origin = $_GET['t'];
		$source = $_GET['c'];
		if(!$origin)die();
		if(!$source)$source = '1';


		//此处以后扩展规则，目前仅有amazon首页
		if(trim($origin) == 'http://www.amazon.com'){
			$target_url = 'http://www.amazon.com/?_encoding=UTF8&camp=1789&creative=9325&linkCode=ur2&tag=44haitaoad1-20';
		}

		$target = urlencode(base64_encode($target_url));

		$this->StatRedirect->save(array('source'=>$source, 'ip'=>getip(), 'area'=>getAreaByIp(), 'client'=>getBrowser(), 'origin'=>$origin, 'target'=>$target_url));

		$this->redirect('http://haitao.44zhe.com/?l=go&r='.$target.'&c='.$source);

	}
}

?>