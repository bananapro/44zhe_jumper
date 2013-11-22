<?php

require './common.php';
require MYLIBS . 'curl.class.php';

set_time_limit(0);

$curl = new CURL();
$curl->cookie_path = '/tmp/cookie_mfp';
$log_dir = './logs/';
mkdir($log_dir);

$domain = 'http://passport.51fanli.com/';
$api = 'http://www.jumper.com/';

function getTask(){
	global $api;
	$task = file_get_contents($api.'target/getTask');
	$task = json_decode($task, true);
	if($task['status'])
		return $task['message'];
}

function finishTask($taskid, $status=2, $error_msg=''){
	global $api;
	if(!$taskid)return;
	if($status != 2){
		echo date('[Y-m-d H:i:s]');
		echo $error_msg;
		echo "\n";
	}

	$task = file_get_contents($api.'target/finishTask/'.$taskid.'/'.$status.'?error_msg='.urlencode($error_msg));
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

	$return = $curl->post('http://sms.51fanli.com/job/smsCode/raw', array('mobile'=>$mobile));
	if($return != 'succ'){
		alert('smsCode', '[post '.$mobile.' task return error]');
		return;
	}

	$timer = 0;
	while(1){

		sleep(10);
		$code = $curl->get('http://sms.51fanli.com/job/smsCode/raw');
		if($code)return array_pop(explode('|', $code));
		$timer++;
		alert('waiting', '[mobile]['.$mobile.' code return]');
		if($timer > 95)return;
	}
}

while(1){

	$t = getTask();
	if(!$t){
		alert('task', 'empty');sleep(10);continue;
	}

	$taskid = $t['id'];

	$time = time();
	$time2 = $time + 2;
	$time4 = $time + 4;
	$rand16 = rand(1000000000000000, 9999999999999999);
	$rand4 = rand(1000,9999);


	$tpl = "{$domain}login/ajaxlogin?jsoncallback=jQuery1720{$rand16}_{$time}{rand4}&username={$t['username']}&userpassword=".md5($t['password'])."&passcode=&cooklogin=1&savename=1&t={$time2}&_={$time4}";

	@unlink($curl->cookie_path);
	$p = getProxy('http://passport.51fanli.com/login');
	if(!$p){
		finishTask($taskid, 0);
		alert('proxy', '[can not select one]');
		continue;
	}else{
		$curl->proxy = $p;
	}

	$return = $curl->get($tpl, "{$domain}login");

	if(stripos($return, '20000')!==false){

		//绑定手机
		$curl->get("{$domain}center/safephone/bindphone1", "{$domain}center/safeuser/safecenter");
		$return = $curl->post("{$domain}center/safephone/ajaxBindPhone1", array('mobile'=>$t['mobile']), "{$domain}center/safephone/bindphone1");
		if(!isSucc($return)){
			finishTask($taskid,  10, '[ajaxBindPhone1]['.$return.']');
			continue;
		}

		$r = $curl->get("{$domain}center/safephone/bindPhone2");
		$return = $curl->get("{$domain}center/safephone/sendverifycode?pos=601&mobile={$t['mobile']}");
		if(!isSucc($return)){
			finishTask($taskid, 10, '[sendverifycode]['.$return.']');
			continue;
		}

		$code = getPhoneCode($curl, $t['mobile']);
		if(!$code){
			finishTask($taskid, 10, '[sendverifycode][get code fail]');
			continue;
		}

		$return = $curl->post("{$domain}center/safephone/ajaxBindPhone2", array('mobile'=>$t['mobile'], 'code'=>$code), "{$domain}center/safephone/bindphone2");
		if(!isSucc($return)){
			finishTask($taskid, 10, '[ajaxBindPhone2]['.$return.']');
			continue;
		}

		//绑定支付宝

		$curl->get("{$domain}center/safeaccount/accountManagement", "{$domain}center/safeuser/safecenter");
		$curl->get("{$domain}center/safeaccount/bindAlipay1_beta", "{$domain}center/safeuser/accountManagement");
		$return = $curl->post("{$domain}center/safeaccount/ajaxBindAlipay1", array('pay_account'=>$t['alipay'], 'realname'=>$t['truename'], 'identify'=>$t['idcard'], 'identify_type'=>1), "{$domain}center/safeaccount/bindAlipay1_beta");
		if(!isSucc($return)){
			finishTask($taskid, 10, '[ajaxBindAlipay1]['.$return.']');
			continue;
		}

		$curl->get("{$domain}center/safeaccount/bindAlipay2");
		$return = $curl->get("{$domain}center/safephone/sendverifycode?pos=906&mobile=");
		if(!isSucc($return)){
			finishTask($taskid, 10, '[sendverifycode2]['.$return.']');
			continue;
		}

		$code = getPhoneCode($curl, $t['mobile']);
		if(!$code){
			finishTask($taskid, 10, '[sendverifycode][get code fail]');
			continue;
		}

		$return = $curl->post("{$domain}center/safeaccount/ajaxBindAlipay2", array('code'=>$code), "{$domain}center/safeaccount/bindAlipay2");
		if(!isSucc($return)){
			finishTask($taskid, 10, '[ajaxBindAlipay2]['.$return.']');
			continue;
		}

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

		finishTask($taskid, 2);
		alert('succ', $t['username']);

	}else{

	    finishTask($taskid, 10, '[login error]['.$return.']');
	}
}

?>
