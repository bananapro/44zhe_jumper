<?php

class RController extends AppController {

	var $name = 'R';
	var $uses = array('StatRedirect', 'UserRebates');
	var $tag = '44haitaoad1-20';

	function _addStat($status=1, $origin, $target_url, $source=1000){

		$detail = array('agent'=>$_SERVER["HTTP_USER_AGENT"], 'language'=>$_SERVER["HTTP_ACCEPT_LANGUAGE"]);
		$this->StatRedirect->save(array('status'=>1, 'source'=>$source, 'ip'=>getip(), 'area'=>getAreaByIp(), 'client'=>getBrowser(), 'origin'=>$origin, 'target'=>$target_url, 'detail'=>json_encode($detail)));
	}

	function index(){

		$origin = $_GET['t'];
		$source = $_GET['c'];
		if(!$origin)die();
		if(!$source)$source = '1';

		$target_url = $origin;
		//此处以后扩展规则
		if(trim($origin) == 'http://www.amazon.com'){
			//$target_url = 'http://www.amazon.com/?_encoding=UTF8&camp=1789&creative=9325&linkCode=ur2&tag=44haitaoad1-20';
			//使用rebates跳转
			$pass = false;
			if (!overLimitDay('JUMP_LIMIT_REBATES') && !@$_COOKIE['channel_amazon']) {
				$user = $this->UserRebates->getUser();
				if($user){
					$pass = true;
					$this->set('user', $user);
					$this->render(null, null, 'views/r/amazon.thtml');
					setcookie("channel_amazon", 1, time() +  24 * 3600, '/'); //进行跳转渠道标志
					$this->_addStat(1, $origin, $target_url, $source);
					die();
				}
			}
			$this->_addStat(0, $origin, $target_url, $source);
			$this->redirect($origin);

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

		$this->_addStat(1, $origin, $target_url, $source);
		$this->redirect('http://haitao.44zhe.com/?l=go&r='.$target.'&c='.$source);

	}
}

?>