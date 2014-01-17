<?php

//穿透代理，用于截获登陆信息/截获跳转信息
require './common.php';

//mission信息存在cookie里面
$mission = proxyGetMission();

$path = $_GET['path'];
unset($_GET['path']);
$uri = $_SERVER["HTTP_HOST"] . $path . '?' . http_build_query($_GET);
$uri = trim($uri, '?');
$page = '';

//进行穿透任务解析
if (@$mission['mission_type'] == 'login') {

	if ($mission['jumper_type'] == 'mizhe') {
		//step 1: 请求登陆页面
		if ($path == '/member/login.html' && $_SERVER['REQUEST_METHOD'] == 'GET') {

			$page = getCacheStatic($uri);
			$page .= "<script>$('#email').val('{$mission['email']}');$('.i-passwd').val('{$mission['password']}')</script>"; //挂入用户名密码
			header('Content-Length: ' . strlen($page)); //修正页面大小
		}

		//step 2: 提交登陆申请
		if ($path == '/member/login.html' && $_SERVER['REQUEST_METHOD'] == 'POST') {

			$proxy->enable_follow = false;
			$page = $proxy->request($uri);

			if (stripos($proxy->response_headers, '302 Moved Temporarily')) {
				$return = loginSucc($mission['jumper_type'], $mission['jumper_uid'], $proxy->response_cookies);
				if ($return) {
					setcookie('carry_mission', '', 0, '/'); //清除任务标识
					//清除mizhe登陆状态
					$page = '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><body><div style="text-align:center;width:100%"><script src="http://www.mizhe.com/member/logout.html"></script><br /><br /><br /><br /><h2><b>该登陆任务处理完毕，请重新领取!</b></h2></div></body></html><script>setTimeout(function(){window.close()}, 3000);</script>';
				}
			}
		}
		//end mizhe login mission
	}

	if ($mission['jumper_type'] == 'geihui') {

		//step 1: 请求登陆页面
		if ($path == '/shop.html' && $_SERVER['REQUEST_METHOD'] == 'GET') {
			$page = getCacheStatic($uri);
			$page = str_replace('<!--end搜索-->', "<script>$('input[name=username]').val('{$mission['email']}');$('input[name=password]').val('{$mission['password']}');$('#rememberme').attr('checked', true);function my_login_hack(){jQuery('#uloginfo').ajaxForm({success:function(data){if(data.status==1)alert('登陆任务完成!');window.close()}, dataType:'json'})};</script>", $page); //挂入用户名密码
			$page = str_replace('init_login_form', 'my_login_hack', $page);
			header('Content-Length: ' . strlen($page)); //修正页面大小
		}

		//step 2: 提交登陆申请
		if ($path == '/user/checklogin' && $_SERVER['REQUEST_METHOD'] == 'POST') {

			$proxy->enable_follow = false;
			//$_COOKIE = array(); //清空cookie，防止前面步骤携带session，该返返回头不会携带session
			$page = $proxy->request($uri);

			if (stripos($page, '"status":1')) {
				$return = loginSucc($mission['jumper_type'], $mission['jumper_uid'], $proxy->response_cookies);
				if ($return) {
					setcookie('carry_mission', '', 0, '/'); //清除任务标识
					//TODO 清除登陆状态
				}
			}
		}
		//end mizhe login mission
	}

	if ($mission['jumper_type'] == 'baobeisha' || $mission['jumper_type'] == 'jsfanli') {

		//step 1: 请求登陆页面
		if ($path == '/index.php' && $_GET['act'] == 'login' && $_SERVER['REQUEST_METHOD'] == 'GET') {
			$page = getCacheStatic($uri);
			$page .= "<script>$('#username').val('{$mission['email']}');$('#password').val('{$mission['password']}');$('#remember').attr('checked', true);"; //挂入用户名密码
			header('Content-Length: ' . strlen($page)); //修正页面大小
		}

		//step 2: 提交登陆申请
		if ($path == '/index.php' && $_GET['act'] == 'login' && $_SERVER['REQUEST_METHOD'] == 'POST') {

			$proxy->enable_follow = false;
			$page = $proxy->request($uri);

			if (stripos($page, 'window.location.href')) {
				$return = loginSucc($mission['jumper_type'], $mission['jumper_uid'], $proxy->response_cookies);
				if ($return) {
					setcookie('carry_mission', '', 0, '/'); //清除任务标识
					//TODO 清除登陆状态
					$page = '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><body><div style="text-align:center;width:100%"><br /><br /><br /><br /><h2><b>该登陆任务处理完毕，请重新领取!</b></h2></div></body></html><script>setTimeout(function(){window.close()}, 3000);</script>';
				}
			}
		}
		//end mizhe login mission
	}

	if ($mission['jumper_type'] == 'taofen8') {

		//step 1: 请求登陆页面
		if ($path == '/container' && $_SERVER['REQUEST_METHOD'] == 'GET') {
			$page = getCacheStatic($uri);
			$page = str_replace("\n", '', $page);
			$page = str_replace('<div id="login">', '<div id="login">用户名： &nbsp; '.$mission['email'].' &nbsp; &nbsp; 密码：&nbsp; &nbsp; '.$mission['password'].'', $page);
			header('Content-Length: ' . strlen($page)); //修正页面大小
		}

		//step 2: 提交登陆申请
		if (isset($_GET['top_parameters']) && $_SERVER['REQUEST_METHOD'] == 'GET') {

			$proxy->enable_follow = true;
			$page = $proxy->request($uri);
			$return = loginSucc($mission['jumper_type'], $mission['jumper_uid'], $proxy->response_cookies);
			if ($return) {
				setcookie('carry_mission', '', 0, '/'); //清除任务标识
				//登出淘宝防止记录登陆状态
				$page = '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><body><div style="text-align:center;width:100%"><script src="https://login.taobao.com/member/logout.jhtml?f=top&out=true&redirectURL=http://www.worker.com/login.php"></script><script src="https://login.taobao.com/member/logout.jhtml?f=top&out=true"></script><script src="https://login.taobao.com/member/login.jhtml?sub=true&style=mini_top&need_sign=top&full_redirect=true&from=mini_top&from_encoding=utf-8#"></script><br /><br /><br /><br /><h2><b>该登陆任务处理完毕，请重新领取!</b></h2></div></body></html><script>setTimeout(function(){window.close()}, 3000);</script>';
			}
		}
		//end mizhe login mission
	}

	if ($mission['jumper_type'] == 'juanpi') {

		//step 1: 请求登陆页面
		if ($path == '/login' && $_SERVER['REQUEST_METHOD'] == 'GET') {
			$page = getCacheStatic($uri);
			$page .= "<script>$('#account').val('{$mission['email']}');$('#password').val('{$mission['password']}');</script>"; //挂入用户名密码
			header('Content-Length: ' . strlen($page)); //修正页面大小
		}

		//step 2: 提交登陆申请
		if ($path == '/login/checkLogin' && $_SERVER['REQUEST_METHOD'] == 'POST') {

			$proxy->enable_follow = false;
			$page = $proxy->request($uri);
			if (stripos($page, 'status":"1"')) {
				$return = loginSucc($mission['jumper_type'], $mission['jumper_uid'], $proxy->response_cookies);
				if ($return) {
					setcookie('carry_mission', '', 0, '/'); //清除任务标识
					//TODO 清除登陆状态
				}
			}
		}

		//step 3: 用户中心
		if ($path == '/' && $_SERVER['REQUEST_METHOD'] == 'GET') {

			$page = '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><body><div style="text-align:center;width:100%"><script src="https://login.taobao.com/member/logout.jhtml?f=top&out=true&redirectURL=http://www.worker.com/login.php"></script><script src="https://login.taobao.com/member/logout.jhtml?f=top&out=true"></script><script src="https://login.taobao.com/member/login.jhtml?sub=true&style=mini_top&need_sign=top&full_redirect=true&from=mini_top&from_encoding=utf-8#"></script><br /><br /><br /><br /><h2><b>该登陆任务处理完毕，请重新领取!</b></h2></div></body></html><script>setTimeout(function(){window.close()}, 3000);</script>';
		}
		//end mizhe login mission
	}
}

if (!$page)
	$page = getCacheStatic($uri);

echo $page;
?>