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
	}
	elseif ($time1 < $time2) {
		$result = -1;
	}
	else {
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
function overlimit_day($var, $d=null) {

	if (!$d)
		$d = date('Ymd');
	$limit = C('config', $var);
	$file = '/tmp/overlimit_day/' . $var . '/' . $d;
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
function overlimit_day_incr($var, $d=null, $incr=1) {

	if (!$d)
		$d = date('Ymd');
	$file = '/tmp/overlimit_day/' . $var . '/' . $d;
	if (is_file($file)) {
		$today = intval(file_get_contents($file));
		$today = $today + $incr;
	}
	else {
		mkdirs($file);
		$today = $incr;
	}

	file_put_contents($file, $today);
	return true;
}

//生成outcode
function getOutCode($iUserId, $sTc) {
	if(!$sTc)$sTc = 't0';
	$sOC = 'B0';
	$sOC.= str_pad(substr(base_convert($iUserId, 10, 36), 0, 6), 6, '0', STR_PAD_LEFT); //6位36进制用户ID,不足前面补0
	$sTc2 = '';
	$sTc2 = str_pad(substr($sTc, 0, 2) , 2, '0', STR_PAD_LEFT);
	$sOC.= "ZZ".$sTc2;
	return $sOC;
}

//识别浏览器
function getBrowser() {


	if (strpos($_SERVER["HTTP_USER_AGENT"], "TheWorld") || strpos($_SERVER["HTTP_USER_AGENT"], "QIHU THEWORLD")) {
		$browser = 'world';
	}
	elseif (strpos($_SERVER["HTTP_USER_AGENT"], "Maxthon")) {
		$browser = 'aoyou';
	}
	elseif (strpos($_SERVER["HTTP_USER_AGENT"], "TencentTraveler")) {
		$browser = 'telcent';
	}
	elseif (strpos($_SERVER["HTTP_USER_AGENT"], "SE 2") AND strpos($_SERVER["HTTP_USER_AGENT"], "MetaSr")) {
		$browser = 'sogou';
	}
	elseif (strpos($_SERVER["HTTP_USER_AGENT"], "360SE") AND !strpos($_SERVER["HTTP_USER_AGENT"], "TencentTraveler")) {
		$browser = 'qihu';
	}
	elseif (strpos($_SERVER["HTTP_USER_AGENT"], "QIHU 360EE") AND !strpos($_SERVER["HTTP_USER_AGENT"], "TencentTraveler")) {
		$browser = 'qihu';
	}
	elseif (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 11.0")) {
		$browser = 'ie11';
	}
	elseif (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 10.0")) {
		$browser = 'ie10';
	}
	elseif (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 9.0")) {
		$browser = 'ie9';
	}
	elseif (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 8.0")) {
		$browser = 'ie8';
	}
	elseif (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 7.0")) {
		$browser = 'ie7';
	}
	elseif (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 6.0")) {
		$browser = 'ie6';
	}
	elseif (strpos($_SERVER["HTTP_USER_AGENT"], "Firefox")) {
		$browser = 'firefox';
	}
	elseif (strpos($_SERVER["HTTP_USER_AGENT"], "Chrome")) {
		$browser = 'chrome';
	}
	elseif (strpos($_SERVER["HTTP_USER_AGENT"], "Safari")) {
		$browser = 'safari';
	}
	elseif (strpos($_SERVER["HTTP_USER_AGENT"], "iphone") || strpos($_SERVER["HTTP_USER_AGENT"], "ipad")) {
		$browser = 'ios';
	}
	elseif (strpos($_SERVER["HTTP_USER_AGENT"], "android")) {
		$browser = 'android';
	}
	else {
		$browser = $_SERVER["HTTP_USER_AGENT"];
	}
	return $browser;
}

//返利网passport接口
function ApiFanliPassport($api, $params, $secret = '9f93eab2452f8dba5c7b9dd49dd85888') {

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
	return 'http://passport.51fanli.com' . $api . '?' . $p;
}

//记录到报警表
function alert($target, $info) {

	if (!$target || !$info) {
		return;
	}

	$db = new Alert();
	$db->create();
	$db->save(array('target' => $target, 'info' => $info, 'ip' => getip(), 'area' => getAreaByIp(), 'client'=>getBrowser()));
	return true;
}

//米折加密链接转换
function decodeMizheLink($link){
	$link = str_replace('_','/',$link);
	$link = str_replace('-','+',$link);
	$link = base64_decode($link);
	return $link;
}

//米折加密链接转换
function encodeMizheLink($link){
	$link = str_replace('http://','',$link);
	$link = str_replace('s.click.taobao.com/','tbkurl/',$link);
	$link = str_replace('g.click.taobao.com/','gurl/',$link);
	$link = str_replace('taobao.com/','tbsite/',$link);
	$link = base64_encode($link);
	$link = str_replace('+','-',$link);
	return $link;
}

//获取代理
function getProxy($p) {

	if (!$p)
		return false;

	$api = "http://www.xinxinproxy.com/httpip/json?count=5&orderId=" . C('config', 'PROXY_ORDER') . "&includeProvinces=" . $p .'&isps=电信,联通,移动,其它运营商&ports=6675,8080,80,6666,8909,其它端口';

	$data = file_get_contents($api);

	if (!$data) {
		$api = "http://backup.xinxinproxy.com/httpip/json?count=5&orderId=" . C('config', 'PROXY_ORDER') . "&includeProvinces=" . $p .'&isps=电信,联通,移动,其它运营商&ports=6675,8080,80,6666,8909,其它端口';
		$data = file_get_contents($api);
	}

	if (!$data) {
		alert('get proxy', 'api return error!');
		return;
	}

	$data = json_decode($data, true);

	if (isset($data['errorCode'])) {
		alert('get proxy', 'errorCode ' . $data['errorCode']);
		return;
	}

	if ($data['availableDate'] < 3) {
		alert('get proxy', 'date expire ' . $data['availableDate']);
	}

	if ($data['remainCount'] < 10) {
		alert('get proxy', 'remainCount less than ' . $data['remainCount']);
	}

	shuffle($data['ips']);
	if(isset($data['ips'][0])){
		$proxy = $data['ips'][0];
	}else{
		alert('get proxy', $p.' proxy empty');
		return;
	}

	return $proxy;
}


//更好的随机算法
function hitRate($total, $curr, $rate){
	if(!$total || !$rate){
		return false;
	}

	$base = $curr/$total;
	if($rate > $base)return true;
}

function taobaoItemDetail($id){

	require MYLIBS . 'taobaoapi' . DS . 'top' . DS . 'TopClient.class.php';
	require MYLIBS . 'taobaoapi' . DS . 'top' . DS . 'request' . DS . 'TbkItemsDetailGetRequest.php';

	//实例化TopClient类
	$client = new TopClient;
	// $client->appkey = '12019508';
	// $client->secretKey = '4c079fe9f7edb17e1878f789d04896cf';
	$client->appkey = '21306056';
	$client->secretKey = 'f0362fe1abacd41cb0f4495c63c9c0c6';
	//$client->fanliNick = '苹果元元88';
	$client->format = 'json';
	$req = new TbkItemsDetailGetRequest;
	$req->setFields("num_iid,seller_id,nick,title,price,volume,pic_url,item_url,shop_url");
	$req->setNumIids($id);
	$resp = $client->execute($req);
	if(!@$resp->code && @$resp->tbk_items){
		foreach ($resp->tbk_items->tbk_item as $item) {
			$num_iid = (string) $item->num_iid;
			$data = array(
						'num_iid' => $item->num_iid,
						'title' => $item->title,
						'nick' => $item->nick,
						'price' => $item->price,
						'fanli' => 1,
						);
			$itemDetailArr[$num_iid] = $data;
		}

		$info = array();
		$info['p_title'] = $itemDetailArr[$id]['title'];
		$info['p_seller'] = $itemDetailArr[$id]['nick'];
		$info['p_price'] = $itemDetailArr[$id]['price'];
		$info['p_fanli'] = $itemDetailArr[$id]['fanli'];
		$info['p_rate'] = $itemDetailArr[$id]['fanli'];

	}else if(@$resp->code){
		//TODO alert 记录错误日志
		alert('taobao api', 'error : [' . $resp->code . ']');
		$info = array();
	}else{
		//无返利
		$info = array();
	}

	return $info;
}

?>