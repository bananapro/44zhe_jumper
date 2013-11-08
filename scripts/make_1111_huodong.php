<?php

require './common.php';
require MYLIBS . 'curl.class.php';

//获取代理
function p() {

    $api = "http://www.xinxinproxy.com/httpip/text?orderId=438892078761457&count=1&includeProvinces=%E4%B8%8A%E6%B5%B7,%E6%9D%AD%E5%B7%9E,%E6%B5%99%E6%B1%9F,%E8%8B%8F%E5%B7%9E,%E5%8D%97%E4%BA%AC&isShuffle=1&isNew=0";

    $data = file_get_contents($api);

    if (!$data) {
        return;
    }

    return $data;
}


$curl = new CURL();
$curl->cookie_path = '/tmp/cookie';
$log_dir = './logs/';
mkdirs($log_dir. 'make.log');

$file = @file_get_contents('./logs/fanli_1111_users');

if(!$file){
    echo "fanli_1111_users  not found!\n";

}else{

	$lines = explode("\n", $file);

	//完成当天的新用户任务
	foreach ($lines as $line) {

	    list($username, $password) = explode("\t", $line);
	    $username = trim($username);
	    $password = md5(trim($password));
	    // $password = md5('bpro880214');

	    if(!$username)continue;

	    $hour = date('H', time());

	    if($hour > 8 && $hour < 24){

		$time = time();
		$time2 = $time + 2;
		$time4 = $time + 4;
		$rand16 = rand(1000000000000000, 9999999999999999);
		$rand4 = rand(1000,9999);

		$tpl = "http://passport.51fanli.com/login/ajaxlogin?jsoncallback=jQuery1720{$rand16}_{$time}{rand4}&username={$username}&userpassword={$password}&passcode=&cooklogin=1&savename=1&t={$time2}&_={$time4}";

		@unlink($curl->cookie_path);

		while(1){

		    $proxy = p();
		    if(!$proxy){
			echo "[".date('Y-m-d H:i')."][proxy_get_empty]\n";
			//sleep(2);
			continue;

		    }else{
			list($curl->proxy['address'], $curl->proxy['port']) = explode(':', $proxy);
			$test = $curl->get('http://go.44zhe.com/default/info/ip');
			if(trim($test) != $curl->proxy['address']){
			    echo "[".date('Y-m-d H:i')."][proxy_bad]\n";
			    //sleep(2);
			    continue;
			}else{
			    break;
			}
		    }
		}
		$return = $curl->get($tpl, 'http://passport.51fanli.com/login');
		if(stripos($return, '20000')!==false){
		    $curl->get('http://huodong.51fanli.com/go1111', 'http://www.51fanli.com/');
		    //sleep(rand(1,6));
		    $curl->get('http://huodong.51fanli.com/go1111/interParticipate', 'http://huodong.51fanli.com/go1111');
		    //sleep(rand(1,5));
		    $curl->get('http://huodong.51fanli.com/go1111', 'http://huodong.51fanli.com/go1111');
		    //sleep(rand(15,25));
		    $return = $curl->get('http://huodong.51fanli.com/go1111/getPrize', 'http://huodong.51fanli.com/go1111');
		    $obj = json_decode($return, true);

		    if($obj['status']){
			file_put_contents($log_dir . '1111_huodong_'.date('Ymd').'.log', "[".date('Y-m-d H:i')."][$username][$return]\n", 8);
			echo "[".date('Y-m-d H:i')."][success] " . $username . " : $return\n";
		    }
		}else{
		    echo "[".date('Y-m-d H:i')."][error] " . $username . "\n";
		}
	    }else{
		sleep((9-$hour)*3600);
	    }
	}
}

//完成全部用户当天抽奖
$all_user = @file_get_contents('./logs/fanli_1111_users_all');
$maked_log = @file_get_contents($log_dir . '1111_huodong_'.date('Ymd').'.log');
$maked_log = str_replace("\n", '|', $maked_log);

$lines = explode("\n", $all_user);
foreach($lines as $line){
	
	list($username, $password) = explode("\t", $line);
	$username = trim($username);
	$password = md5(trim($password));
	$hour = date('H', time());
	
	if(stripos($maked_log, $username)!==false)continue;

	if(($hour > 7 && $hour < 24) || $hour < 2){

		$time = time();
		$time2 = $time + 2;
		$time4 = $time + 4;
		$rand16 = rand(1000000000000000, 9999999999999999);
		$rand4 = rand(1000,9999);

		$tpl = "http://passport.51fanli.com/login/ajaxlogin?jsoncallback=jQuery1720{$rand16}_{$time}{rand4}&username={$username}&userpassword={$password}&passcode=&cooklogin=1&savename=1&t={$time2}&_={$time4}";

		@unlink($curl->cookie_path);

		while(1){

		    $proxy = p();
		    if(!$proxy){
			echo "[".date('Y-m-d H:i')."][proxy_get_empty]\n";
			//sleep(2);
			continue;

		    }else{
			list($curl->proxy['address'], $curl->proxy['port']) = explode(':', $proxy);
			$test = $curl->get('http://go.44zhe.com/default/info/ip');
			if(trim($test) != $curl->proxy['address']){
			    echo "[".date('Y-m-d H:i')."][proxy_bad]\n";
			    //sleep(2);
			    continue;
			}else{
			    break;
			}
		    }
		}

		$return = $curl->get($tpl, 'http://passport.51fanli.com/login');
		if(stripos($return, '20000')!==false){
		    $curl->get('http://huodong.51fanli.com/go1111', 'http://www.51fanli.com/');
                    sleep(rand(5,10));
		    $return = $curl->get('http://huodong.51fanli.com/go1111/getPrize', 'http://huodong.51fanli.com/go1111');
		    file_put_contents($log_dir . '1111_huodong_'.date('Ymd').'.log', "[".date('Y-m-d H:i')."][$username][$return]\n", 8);
		    echo "[".date('Y-m-d H:i')."][success] " . $username . " : $return\n";
		}else{
		    echo "[".date('Y-m-d H:i')."][error] " . $username . "\n";
		}
	}else{
		sleep((6-$hour)*3600);
	}
}
?>
