<?php

require './common.php';
require MYLIBS . 'curl.class.php';

set_time_limit(0);

$curl = new CURL();
$curl->timeout = 30;
$curl->cookie_path = '/tmp/cookie_mfp';
$log_dir = './logs/';
@mkdir($log_dir);

$domain = 'http://passport.51fanli.com/';
$api = 'http://www.jumper.com/';

function getTask(){
	global $api;
	$task = file_get_contents($api.'target/getTask');
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
		finishTask($taskid, 17, 'profile尝试超过3次，暂时跳过');
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
		finishTask($taskid, 0);
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

		if($t['status'] ==0){
			finishTask($taskid, 1, '', $ret['data']['u_id']);
			$t['status'] = 1;
		}

		$cookie = buildCookie(array('PHPSESSID'=>$ret['data']['sid'], 'prouserid'=>$ret['data']['u_id'], 'prousernameutf'=>urlencode($t['username'])));
		//echo $cookie;die();
		file_put_contents($curl->cookie_path, $cookie);

		if($t['status'] == 1){
			//绑定手机
			$curl->get("{$domain}center/safephone/bindphone1", "{$domain}center/safeuser/safecenter");
			$return = $curl->post("{$domain}center/safephone/ajaxBindPhone1", array('mobile'=>$t['mobile']), "{$domain}center/safephone/bindphone1");
			if(!isSucc($return) && stripos($return, '您已绑定手机')===false){
				finishTask($taskid,  1, '[ajaxBindPhone1]['.$return.']');
				continue;
			}

			if(stripos($return, '您已绑定手机')!==false){
				finishTask($taskid, 2);
				$t['status'] = 2;
			}elseif(stripos($return, '您已经提交了手机绑定')!==false){
				finishTask($taskid, 18, '您已经提交了手机绑定');
				continue;
			}else{
				$r = $curl->get("{$domain}center/safephone/bindPhone2");
				$return = $curl->get("{$domain}center/safephone/sendverifycode?pos=601&mobile={$t['mobile']}");
				if(!isSucc($return)){
					finishTask($taskid, 1, '[sendverifycode1]['.$return.']');
					continue;
				}

				$code = getPhoneCode($curl, $t['mobile']);
				if(!$code){
					finishTask($taskid, 1, '[sendverifycode1][get code fail]');
					continue;
				}

				$return = $curl->post("{$domain}center/safephone/ajaxBindPhone2", array('mobile'=>$t['mobile'], 'code'=>$code), "{$domain}center/safephone/bindphone2");
				if(!isSucc($return)){
					finishTask($taskid, 1, '[ajaxBindPhone2]['.$return.']');
					continue;
				}
				finishTask($taskid, 2);
				$t['status'] = 2;
			}

			alert('bind', '[mobile]['.$t['mobile'].'][ok]');
		}

		if($t['status'] == 2){
			//绑定支付宝

			$curl->get("{$domain}center/safeaccount/accountManagement", "{$domain}center/safeuser/safecenter");
			$curl->get("{$domain}center/safeaccount/bindAlipay1_beta", "{$domain}center/safeuser/accountManagement");
			$return = $curl->post("{$domain}center/safeaccount/ajaxBindAlipay1", array('pay_account'=>$t['alipay'], 'realname'=>$t['truename'], 'identify'=>$t['idcard'], 'identify_type'=>1), "{$domain}center/safeaccount/bindAlipay1_beta");
			if(!isSucc($return)){
				finishTask($taskid, 2, '[ajaxBindAlipay1]['.$return.']');
				continue;
			}

			$curl->get("{$domain}center/safeaccount/bindAlipay2");
			$return = $curl->get("{$domain}center/safephone/sendverifycode?pos=906&mobile=");
			if(!isSucc($return)){
				finishTask($taskid, 2, '[sendverifycode2]['.$return.']');
				continue;
			}

			$code = getPhoneCode($curl, $t['mobile']);
			if(!$code){
				finishTask($taskid, 2, '[sendverifycode2][get code fail]');
				continue;
			}

			$return = $curl->post("{$domain}center/safeaccount/ajaxBindAlipay2", array('code'=>$code), "{$domain}center/safeaccount/bindAlipay2");
			if(!isSucc($return)){
				finishTask($taskid, 2, '[ajaxBindAlipay2]['.$return.']');
				continue;
			}
			finishTask($taskid, 3);
			alert('bind', '[alipay]['.$t['alipay'].'][ok]');

			$data = array();
			$data['smsset[od]'] = 0;
			$data['smsset[fl]'] = 0;
			$data['smsset[fl_mode]'] = 2;
			$data['smsset[point]'] = 0;
			$data['smsset[cash]'] = 0;
			$data['smsset[g_refund]'] = 0;
			$data['smsset[g_appeal]'] = 0;
			$data['smsset[g_refuse]'] = 0;
			$data['mailset[g_refuse]'] = 0;

			$curl->post("{$domain}center/safephone/savenotify", $data, "{$domain}center/safeuser/safecenter");

			alert('task', '[end]['.$t['username'].'][ok]');
		}

	//20002
	}elseif(stripos($return, '20002')!==false || stripos($return, '2002')!==false){
		finishTask($taskid, 19, '[login error]['.$return.']');

	}elseif(stripos($return, 'connect')!==false || stripos($return, 'operation')!==false || stripos($return, 'empty')!==false){

	    finishTask($taskid, 0, '[login error]['.$return.']');
	}else{
		finishTask($taskid, 0, '[login error]['.$return.']');
	}

	alert('sleep', '10 second ...');
	echo "\n";
	sleep(10);

}

?>
