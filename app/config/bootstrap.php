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


/**
 * 生成outcode;
 * @param type $iUserId
 * @param type $sTc
 * @return string
 */
function getOutCode($iUserId, $sTc = 'a4') {
    $sOC = 'A0';
    $sOC.= str_pad(substr(base_convert($iUserId, 10, 36), 0, 6), 6, '0', STR_PAD_LEFT); //6位36进制用户ID,不足前面补0
    $sOC.= str_pad(substr($sTc, 0, 2), 2, '0', STR_PAD_LEFT); //两位跟踪码，不足前面补0
    $sOC.= base_convert(date('m'), 10, 36) . base_convert(date('d'), 10, 36);
    return $sOC;
}


?>