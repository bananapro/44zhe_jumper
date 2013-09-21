<?php

//穿透代理，用于截获登陆信息/截获跳转信息

require './common.php';

//mission信息存在cookie里面
$mission = proxyGetMission();

$path = $_GET['path'];
unset($_GET['path']);


//进行穿透任务解析
if (@$mission['mission_type'] == 'login') {

	if ($mission['jumper_type'] == 'mizhe') {
		//step 1: 请求登陆页面
		if ($path == '/member/login.html' && $_SERVER['REQUEST_METHOD'] == 'GET') {

			$page = getCacheStatic($_SERVER["HTTP_HOST"] . $path . '?' . http_build_query($_GET));
			$page .= "<script>$('#email').val('{$mission['email']}');$('.i-passwd').val('{$mission['password']}')</script>"; //挂入用户名密码
			header('Content-Length: ' . strlen($page)); //修正页面大小
		}

		//step 2: 提交登陆申请
		if ($path == '/member/login.html' && $_SERVER['REQUEST_METHOD'] == 'POST') {

			$proxy->enable_follow = false;
			$_COOKIE = array();//清空cookie，防止前面步骤携带session，该返返回头不会携带session
			$page = $proxy->request($_SERVER["HTTP_HOST"] . $path . '?' . http_build_query($_GET));

			if (stripos($proxy->response_headers, '302 Moved Temporarily')) {
				$return = loginSucc($mission['jumper_type'], $mission['jumper_uid'], $proxy->response_cookies);
				if ($return) {
					setcookie('carry_mission', '', 0, '/');//清除任务标识
					$page = '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><body><div style="text-align:center;width:100%"><br /><br /><br /><br /><h2><b>该登陆任务处理完毕，请重新领取!</b></h2></div></body></html><script>setTimeout(function(){window.close()}, 3000);</script>';
				}
			}
		}
		//end mizhe login mission
	}
}
else {
	$page = getCacheStatic($_SERVER["HTTP_HOST"] . $path . '?' . http_build_query($_GET));
}

echo $page;
?>