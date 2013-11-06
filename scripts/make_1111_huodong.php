<?php

require './common.php';
require MYLIBS . 'curl.class.php';

//获取代理
function p() {

    $api = "http://www.xinxinproxy.com/httpip/text?orderId=438892078761457&count=1&includeProvinces=%E4%B8%8A%E6%B5%B7,%E6%9D%AD%E5%B7%9E,%E6%B5%99%E6%B1%9F,%E8%8B%8F%E5%B7%9E,%E5%8D%97%E4%BA%AC&isShuffle=1&isNew=1";

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

$file = @file_get_contents('./logs/fanli_1111_login_usernames');

if(!$file){
    echo "fanli_login_usernames  not found!\n";
    die();
}

$lines = explode("\n", $file);

foreach ($lines as $line) {

    list($username, $password) = explode("\t", $line);
    $username = trim($username);
    $password = md5(trim($password));
    // $password = md5('bpro880214');

    if(!$username)continue;
    $succ_user = @file_get_contents($log_dir . '1111_huodong.log');
    $succ_user = str_replace("\n", '', $succ_user);

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
                sleep(2);
                continue;

            }else{
                list($curl->proxy['address'], $curl->proxy['port']) = explode(':', $proxy);
                $test = $curl->get('http://go.44zhe.com/default/info/ip');
                if(trim($test) != $curl->proxy['address']){
                    echo "[".date('Y-m-d H:i')."][proxy_bad]\n";
                    sleep(2);
                    continue;
                }else{
                    break;
                }
            }
        }

        $return = $curl->get($tpl, 'http://passport.51fanli.com/login');
        if(stripos($return, '20000')!==false){
            $curl->get('http://huodong.51fanli.com/go1111', 'http://www.51fanli.com/');
	    sleep(rand(1,6));
            $curl->get('http://huodong.51fanli.com/go1111/interParticipate', 'http://huodong.51fanli.com/go1111');
	    sleep(rand(1,5));
            $curl->get('http://huodong.51fanli.com/go1111', 'http://huodong.51fanli.com/go1111');
	    sleep(rand(15,25));
            $return = $curl->get('http://huodong.51fanli.com/go1111/getPrize', 'http://huodong.51fanli.com/go1111');
            file_put_contents($log_dir . '1111_huodong.'.date('Ymd').'.log', "[".date('Y-m-d H:i')."][$username][$return]\n", 8);
            echo "[".date('Y-m-d H:i')."][success] " . $username . " : $return\n";
        }else{
            echo "[".date('Y-m-d H:i')."][error] " . $username . "\n";
        }
    }
}


?>
