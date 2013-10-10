<?php

require './common.php';
require MYLIBS . 'curl.class.php';

//获取代理
function p() {

    $api = "http://www.xinxinproxy.com/httpip/text?orderId=423612754381457&count=1&includeProvinces=%E4%B8%8A%E6%B5%B7,%E6%9D%AD%E5%B7%9E,%E6%B5%99%E6%B1%9F,%E8%8B%8F%E5%B7%9E,%E5%8D%97%E4%BA%AC&isShuffle=1&isNew=1";

    $data = file_get_contents($api);

    if (!$data) {
        return;
    }

    return $data;
}

$hour = date('H', time());
$curl = new CURL();
$curl->cookie_path = '/tmp/cookie';
$log_dir = './logs/';
mkdirs($log_dir. 'login_succ.log');

$file = file_get_contents('./make_fanli_login_usernames.txt');

$lines = explode("\n", $file);

foreach ($lines as $username) {

    $username = trim($username);
    $password = md5($username.'0a');
    // $password = md5('bpro880214');

    if(!$username)continue;
    $succ_user = @file_get_contents($log_dir . 'login_succ.log');
    $succ_user = str_replace("\n", '|', $succ_user);

    if(stripos($succ_user, $username.'|')!==false){
        echo "[".date('Y-m-d H:i')."][skip] " . $username . "\n";
        continue;
    }

    if($hour > 8 && $hour < 24){

        $time = time();
        $time2 = $time + 2;
        $time4 = $time + 4;
        $rand16 = rand(1000000000000000, 9999999999999999);
        $rand4 = rand(1000,9999);

        $tpl = "http://passport.51fanli.com/login/ajaxlogin?jsoncallback=jQuery1720{$rand16}_{$time}{rand4}&username={$username}&userpassword={$password}&passcode=&cooklogin=1&savename=1&t={$time2}&_={$time4}";

        @unlink($curl->cookie_path);

        $proxy = p();
        if(!$proxy){
            echo "[".date('Y-m-d H:i')."][proxy_get_empty]";
            sleep(2);
            continue;

        }else{
            list($curl->proxy['address'], $curl->proxy['port']) = explode(':', $proxy);
            $test = $curl->get('go.44zhe.com/hello.html');
            if(trim($test) != 'hello'){
                echo "[".date('Y-m-d H:i')."][proxy_bad]";
                sleep(2);
                continue;
            }
        }
        $return = $curl->get($tpl, 'http://passport.51fanli.com/login');
        if(stripos($return, '20000')!==false){
            $curl->get('http://passport.51fanli.com/center/safeuser/safecenter', 'http://www.51fanli.com/');
            $curl->get('http://www.51fanli.com/');
            file_put_contents($log_dir . 'login_succ.log', "$username\n", 8);
            echo "[".date('Y-m-d H:i')."][success] " . $username . "\n";
        }else{
            file_put_contents($log_dir . 'login_err.log', "$username\n", 8);
            echo "[".date('Y-m-d H:i')."][error] " . $username . "\n";
        }
    }

    sleep(100);
}


?>