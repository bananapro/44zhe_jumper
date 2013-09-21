<?PHP

require './common.php';

//取登陆任务
$mission = getLoginFailCookies();

if (@$_GET['mission_begin']) {

	if (!$mission) {
		$page = '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><body><div style="text-align:center;width:100%"><br /><br /><br /><br /><h2><b>该登陆任务已完成，请返回刷新！</b></h2></div></body></html><script>setTimeout(function(){window.close()}, 3000);</script>';
		echo $page;die();
	}
	else {

		//跳转到第一个登陆任务
		foreach($mission as $type => $uid){
			$uid = array_shift(array_keys($uid));
			switch($type){
				case 'mizhe':
					$login_url = 'http://www.mizhe.com/member/login.html?carry_mission=login:'.$type.':'.$uid;
					break;
				default:
					die('无法处理该渠道登陆任务：' . $type);
			}
			header('Location: ' . $login_url);die();
		}
	}
}


if ($mission) {
	$msg = '当前有新的登陆任务需要完成，<a href="/login.php?mission_begin=1" target="_blank">点击开始处理</a>';
}
else {
	$msg = '当前无登陆任务';
}

$page = <<<EOT
<html><title>登陆任务处理</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><body>
<div style="text-align:center;width:100%"><br /><br /><br /><br /><br /><br /><br />
<h2><span id="title"><b>$msg</b> ，刷新计时：<span id="count">30</span>s</span></h2></div></body></html>
<script>
 var q = setInterval(function(){
if(parseInt(document.getElementById('count').innerHTML) == 0){
	location.reload();
	clearInterval(q);
}else{
	document.getElementById('count').innerHTML = parseInt(document.getElementById('count').innerHTML) - 1;
}}, 1000);
</script>
EOT;

echo $page;
?>