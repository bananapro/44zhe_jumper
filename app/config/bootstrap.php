<?php

//拼接ocp图片裁剪接口
function imageUrl($path, $size, $cut = 1, $ext = 'jpg') {
    if (!$path)
        return;
    return DOMAIN . '/ocp?url=' . $path . '&size=' . $size . '&quality=85&cut=' . $cut . '&t=' . $ext;
}

//比较两个datetime，$com为比较位，直接返回结果
function com2day($day1, $day2, $com = null) {

    $time1 = strtotime($day1);
    $time2 = strtotime($day2);

    $result = 0;
    if ($time1 > $time2) {
        $result = 1;
    } elseif ($time1 < $time2) {
        $result = -1;
    } else {
        $result = 0;
    }
    if ($com !== null) {
        if ($com === $result)
            return true;
        else
            return false;
    }else {
        return $result;
    }
}

/**
 * 根据配置文件看计数是否超过每日限额
 * @param type $var
 * @return boolean
 */
function overlimit_day($var) {
    $limit = C('config', $var);
    $file = '/tmp/overlimit_day/' . $var . '/' . date('Ymd');
    if (is_file($file)) {
        $today = intval(file_get_contents($file));
        if ($today >= $limit)
            return true;
    }else {
        mkdirs($file);
        file_put_contents($file, 1);
    }

    return false;
}

/**
 * 累计今日指定计数器
 * @param type $var
 * @return boolean
 */
function overlimit_day_incr($var) {
    $file = '/tmp/overlimit_day/' . $var . '/' . date('Ymd');
    if (is_file($file)) {
        $today = intval(file_get_contents($file));
        $today = $today + 1;
    } else {
        mkdirs($file);
        $today = 1;
    }

    file_put_contents($file, $today);
    return true;
}

//生成outcode
function getOutCode($iUserId, $sTc = 'a4') {
    $sOC = 'A0';
    $sOC.= str_pad(substr(base_convert($iUserId, 10, 36), 0, 6), 6, '0', STR_PAD_LEFT); //6位36进制用户ID,不足前面补0
    $sOC.= str_pad(substr($sTc, 0, 2), 2, '0', STR_PAD_LEFT); //两位跟踪码，不足前面补0
    $sOC.= base_convert(date('m'), 10, 36) . base_convert(date('d'), 10, 36);
    return $sOC;
}

//识别浏览器
function getBrowser() {

    
    if (strpos($_SERVER["HTTP_USER_AGENT"], "TheWorld") || strpos($_SERVER["HTTP_USER_AGENT"], "QIHU THEWORLD")) {
        $browser = 'world';
    } elseif (strpos($_SERVER["HTTP_USER_AGENT"], "Maxthon")) {
        $browser = 'aoyou';
    } elseif (strpos($_SERVER["HTTP_USER_AGENT"], "TencentTraveler")) {
        $browser = 'telcent';
    } elseif (strpos($_SERVER["HTTP_USER_AGENT"], "SE 2") AND strpos($_SERVER["HTTP_USER_AGENT"], "MetaSr")) {
        $browser = 'sogou';
    } elseif (strpos($_SERVER["HTTP_USER_AGENT"], "360SE") AND !strpos($_SERVER["HTTP_USER_AGENT"], "TencentTraveler")) {
        $browser = 'qihu';
    } elseif (strpos($_SERVER["HTTP_USER_AGENT"], "QIHU 360EE") AND !strpos($_SERVER["HTTP_USER_AGENT"], "TencentTraveler")) {
        $browser = 'qihu';
    } elseif (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 11.0")) {
        $browser = 'ie11';
    } elseif (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 10.0")) {
        $browser = 'ie10';
    } elseif (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 9.0")) {
        $browser = 'ie9';
    } elseif (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 8.0")) {
        $browser = 'ie8';
    } elseif (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 7.0")) {
        $browser = 'ie7';
    } elseif (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 6.0")) {
        $browser = 'ie6';
    } elseif (strpos($_SERVER["HTTP_USER_AGENT"], "Firefox")) {
        $browser = 'firefox';
    } elseif (strpos($_SERVER["HTTP_USER_AGENT"], "Chrome")) {
        $browser = 'chrome';
    } elseif (strpos($_SERVER["HTTP_USER_AGENT"], "Safari")) {
        $browser = 'safari';
    } elseif (strpos($_SERVER["HTTP_USER_AGENT"], "iphone") || strpos($_SERVER["HTTP_USER_AGENT"], "ipad")) {
        $browser = 'ios';
    } elseif (strpos($_SERVER["HTTP_USER_AGENT"], "android")) {
        $browser = 'android';
    } else {
        $browser = $_SERVER["HTTP_USER_AGENT"];
    }
    return $browser;
}

//返利网passport接口
function ApiFanliPassport($api ,$params, $secret = '9f93eab2452f8dba5c7b9dd49dd85888'){
    
    $tmp = array();
    
    $params['t'] = time();
    $params['ip'] = '127.0.0.1';
    ksort($params);
    
    foreach ($params as $key => $val) {
        $tmp[] = $key . $val;
    }
    $tmp = implode('', $tmp);
    
    $params['sn'] = md5($tmp . $secret);
    foreach ($params as $key => $value) {
        $p[] = rawurlencode($key) . '=' . rawurlencode($value);
    }
    $p = implode("&", $p);
    return 'http://passport.51fanli.com'.$api.'?'.$p;
}

?>