<?php

require './common.php';
require MYLIBS . 'curl.class.php';

set_time_limit(0);

$curl = new CURL();
$curl->cookie_path = '/tmp/cookie_mfp';
$log_dir = './logs/';
@mkdir($log_dir);

$domain = 'http://passport.51fanli.com/';
$api = 'http://www.jumper.com/';

function getTask(){
	global $api;
	$task = file_get_contents($api.'target/getTask/1');
	$task = json_decode($task, true);
	if($task['status'])
		return $task['message'];
}

function finishTask($taskid, $status=2, $error_msg='', $user_id=0){
	global $api;
	if(!$taskid)return;
	if($error_msg){
		echo date('[Y-m-d H:i:s]');
		echo $error_msg;
		echo "\n";
		echo "\n";
	}

	$task = file_get_contents($api.'target/finishTask/'.$taskid.'/'.$status.'/'.$user_id.'?error_msg='.urlencode($error_msg));
}

function isSucc($return){

	if(!$return) return false;
	$r = json_decode($return, true);
	if($r['status'] == 1){
		if($r['info'] == 'success') return true;
	}

	if($r['data'] == '131001'){
		return true;
	}
	return false;
}

function getPhoneCode($curl, $mobile){

	$proxy = $curl->proxy;
	$curl->proxy = false;
	$return = $curl->post('http://sms.51fanli.com/job/smsCode/raw', array('mobile'=>$mobile));
	if($return != 'succ'){
		alert('smsCode', '[post '.$mobile.' task return error]');
		return;
	}

	$timer = 0;
	while(1){

		alert('smsCode', '[waiting]['.$mobile.' code return]');
		sleep(rand(15,20));
		$code = $curl->get('http://sms.51fanli.com/job/smsCode/raw');
		if($code){
			$curl->proxy = $proxy;
			return array_pop(explode('|', $code));
		}
		$timer++;
		if($timer > 95)return;
	}
}

function buildCookie($cookies){

	$expires = time() + 365 * 86400;
	foreach ($cookies as $key=>$value) {
		$e[] = ".51fanli.com\tTRUE\t/\tFALSE\t{$expires}\t{$key}\t{$value}";
	}
	$e = join("\n", $e);
	return $e;
}

$try = array();

while(1){

	$t = getTask();
	if(!$t){
		alert('task', 'empty');sleep(10);continue;
	}

	$taskid = $t['id'];

	//次数保护
	@$try[$t['username']]++;
	if($try[$t['username']]>3){
		finishTask($taskid, 17, '提现尝试超过3次，暂时跳过');
		unset($try[$t['username']]);
		continue;
	}

	alert('task', '[start]['.$t['username'].']');

	$time = time();
	$time2 = $time + 2;
	$time4 = $time + 4;
	$rand16 = rand(1000000000000000, 9999999999999999);
	$rand4 = rand(1000,9999);


	//$tpl = "{$domain}login/ajaxlogin?jsoncallback=jQuery1720{$rand16}_{$time}{$rand4}&username={$t['username']}&userpassword=".md5($t['password'])."&passcode=&cooklogin=1&savename=1&t={$time2}&_={$time4}";
	$tpl = "http://fun.51fanli.com/Api/user/userLogin";

	@unlink($curl->cookie_path);
	alert('proxy', '[begin selecting ...]');
	$p = getProxy('http://passport.51fanli.com/login', 3);
	if(!$p){
		finishTask($taskid, 3);
		alert('proxy', '[can not select one]');
		continue;
	}else{
		$curl->proxy = $p;
		alert('proxy', "[{$p['address']}][{$p['province']}]");
	}

	alert('login', '[begin login ...]');
	$return = $curl->post($tpl, array('account_name'=>$t['username'], 'userpw'=>md5($t['password'])));

	if(stripos($return, 'success')!==false){

		$ret = json_decode($return, true);

		$cookie = buildCookie(array('PHPSESSID'=>$ret['data']['sid'], 'prouserid'=>$ret['data']['u_id'], 'prousernameutf'=>urlencode($t['username'])));
		//echo $cookie;die();
		file_put_contents($curl->cookie_path, $cookie);

		//提现
		$return = $curl->get("{$domain}center/point", "http://www.51fanli.com/profile.asp?action=welcome");
		//echo $return;
		$m = '';
		if(preg_match('/data-drawmaxnum="([0-9]+?)"/i', $return, $m)){

			$point = $m[1];

			$return = $curl->get("{$domain}center/safephone/sendverifycode?pos=1301&vertype=voice");
			if(!isSucc($return)){
				finishTask($taskid, 3, '[sendverifycode_duixian]['.$return.']');
				continue;
			}

			$code = getPhoneCode($curl, $t['mobile']);

			if($point > 0){
				$data = array();
				$data['pay_account'] = $t['alipay'];
				$data['points'] = $point;
				$data['code'] = $code;

				$ret = $curl->post("{$domain}center/point/ajaxsafeverify", $data, "{$domain}center/point");
				$ret = $curl->post("{$domain}center/point/ajaxexchangepoint", $data, "{$domain}center/point");
				if(!isSucc($ret)){
					finishTask($taskid, 3, '[ajaxsafeverify]['.$ret.']');
					continue;
				}
			}else{
				finishTask($taskid, 3, '[get point num error]');
				continue;
			}
		}else{
			finishTask($taskid, 3, '[get point error]');
			continue;
		}

		finishTask($taskid, 4);
		alert('duixian', '[money:'.number_format($point/100, 2).'][ok]');

	//20002
	}elseif(stripos($return, '20002')!==false || stripos($return, '2002')!==false){
		finishTask($taskid, 19, '[password error]['.$return.']');
		continue;

	}elseif(stripos($return, 'connect')!==false || stripos($return, 'operation')!==false || stripos($return, 'empty')!==false){

	    finishTask($taskid, 3, '[login error]['.$return.']');
	    sleep(10);
		continue;

	}else{
		finishTask($taskid, 3, '[login error]['.$return.']');

		sleep(10);
		continue;
	}

	alert('sleep', '90 second ...');
	echo "\n";
	sleep(90);
}

?>
