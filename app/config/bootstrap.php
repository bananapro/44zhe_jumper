<?php

//拼接ocp图片裁剪接口
function imageUrl($path, $size, $cut=1, $ext='jpg') {
	if (!$path)
		return;
	return DOMAIN . '/ocp?url=' . $path . '&size=' . $size . '&quality=85&cut=' . $cut . '&t=' . $ext;
}

function nextSunday($date=null) {

	if(!$date || strtotime($date)<=0)
		$date = date('Y-m-d');

	$day_diff = strtotime($date) - strtotime(date('Y-m-d',time()));
	if($day_diff<0)$day_diff=0;
	$day_diff = intval($day_diff/(24*3600));

	$time = strtotime($date);
	$d = date('w', $time);
	if($d == 0)$d = 7;
	$diff = 7 - $d + $day_diff;
	return date('Y-m-d', strtotime('+' . $diff . 'days'));
}

//比较两个datetime，$com为比较位，直接返回结果
function com2day($day1, $day2, $com=null){

	$time1 = strtotime($day1);
	$time2 = strtotime($day2);

	$result = 0;
	if($time1 > $time2){
		$result = 1;
	}elseif($time1 < $time2){
		$result = -1;
	}else{
		$result = 0;
	}
	if($com !== null){
		if($com === $result)
			return true;
		else
			return false;
	}else{
		return $result;
	}
}

//计算上损耗值后的数量
function loss($num, $rate = 100){

	$rate = $rate/100;
	return number_format($num*(1+$rate/(1-$rate)), 2);
}

?>