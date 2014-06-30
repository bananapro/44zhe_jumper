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
function overLimitDay($var, $d=null) {

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
function overLimitDayIncr($var, $d=null, $incr=1) {

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
//level(1-3) 3最高
function alert($type, $info, $level=1, $uniq=false) {

	if (!$type || !$info) {
		return;
	}

	if(IS_CLI){
		echo date('[Y-m-d H:i:s]');
		echo "[$type]$info\n";
		return;
	}

	$db = new Alert();
	if($uniq){
		$last = $db->find('', '', 'id desc', 1);
		clearTableName($last);
		if($last['ip'] == getIp() && $last['client'] == getBrowser() && $last['type'] == $type && $last['info'] == $info)return;
	}
	$db->create();
	$db->save(array('type' => $type, 'info' => $info, 'ip' => getIp(), 'area' => getAreaByIp(), 'client'=>getBrowser(), 'level'=>$level));
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
function getProxy($test_url = 'http://www.baidu.com', $seed = 3, $try = 3, $p = '上海,北京') {

	if($try < 1)return;

	require_once MYLIBS . 'curl.class.php';

	$api = "http://www.xinxinproxy.com/httpip/json?count={$seed}&orderId=" . C('config', 'PROXY_ORDER') . "&includeProvinces=" . urlencode($p) .'&isps=' . urlencode('电信') . '&isnew=1&isShuffle=1';

	$data = file_get_contents($api);
	$data = json_decode($data, true);

	if(!$data){
		alert('get proxy', '[error][content empty]');
		return;
	}

	if (isset($data['errorCode'])) {
		alert('get proxy', 'errorCode ' . $data['errorCode']);
		return;
	}

	if ($data['availableDate'] < 3) alert('get proxy', '[warning][expire ' . $data['availableDate'] . ']');
	if ($data['remainCount'] < 10) alert('get proxy', '[warning][remainCount less than ' . $data['remainCount'] . ']');
	//test speed
	$curl = new CURL;
	$curl->cookie_path = '/tmp/cookie_proxy';
	$curl->timeout = 2;
	$curl->cntimeout = 2;
	$timer = array();
	$start = getMicrotime();
	foreach($data['ips'] as $proxy){
		if(!$proxy){
			alert('get proxy', '[error][empty]');
			continue;
		}
		$start = getMicrotime();

		$curl->proxy = $proxy;
		$test_ip = $curl->get('http://go.44zhe.com/default/info/ip');
		if(trim($test_ip) != $curl->proxy['address'])continue;

		$curl->timeout = 10;
		$curl->cntimeout = 10;
		$test_url = $curl->get($test_url);
		if(strlen($test_url)<100)continue;
		$timer[intval(round(getMicrotime()-$start, 3)*1000)] = $curl->proxy;
	}

	$selected = false;

	if($timer){
		ksort($timer);
		return array_shift($timer);
	}else{
		return getProxy($test_url, $seed, $try-1, $p);
	}

}

//更好的随机算法
function hitRate($total, $curr, $rate){
	if(!$total || !$rate){
		return false;
	}

	$base = $curr/$total;
	if($rate > $base)return true;
}

function taobaoItemDetail($p_id, $bak_channel = false){
	$stat_obj = new StatApi();

	$cache = $stat_obj->find(array('p_id'=>$p_id, 'created'=>date('Y-m-d')));
	clearTableName($cache);
	if($cache)return json_decode($cache['content'], true);

	require_once MYLIBS . 'api/taobao/top/TopClient.class.php';
	require_once MYLIBS . 'api/taobao/top/request/TbkItemsDetailGetRequest.php';

	//实例化TopClient类
	$client = new TopClient;
	if($bak_channel){

		$client->appkey = '12019508';
		$client->secretKey = '4c079fe9f7edb17e1878f789d04896cf';
		alert('taobao api', '[warning][switch]['.$client->appkey.']');

	}else{

		$rd = rand(1,10);
		if($rd < 8){
			$client->appkey = '21074255';
			$client->secretKey = 'ff2712ae1ad2f824259107b06188bcb8';
		}else{
			$client->appkey = '21306056';
			$client->secretKey = 'f0362fe1abacd41cb0f4495c63c9c0c6';
		}
	}

	//$client->fanliNick = '苹果元元88';
	$client->format = 'json';
	$req = new TbkItemsDetailGetRequest;
	$req->setFields("num_iid,seller_id,nick,title,price,volume,pic_url,item_url,shop_url");
	$req->setNumIids($p_id);
	$resp = $client->execute($req);

	// if($client->appkey == '21306056')$resp->code = 7;
	if(!@$resp->code && @$resp->tbk_items){

		$item = $resp->tbk_items->tbk_item[0];
		$info = array();
		$info['p_title'] = $item->title;
		$info['p_seller'] = $item->nick;
		$info['p_price'] = $item->price;
		$info['p_fanli'] = 1;
		$info['p_rate'] = 1;
		$info['channel'] = $client->appkey;

		if($info['p_title'])
			$stat_obj->add(1, 'succ', $p_id, json_encode($info), $info['channel']);
		else
			alert('taobao api', '[error][api fatal error]['.$p_id.'][!!!!!!!!!!!]');

	}else if(@$resp->code){
		//TODO alert 记录错误日志
		alert('taobao api', '[error][' . $resp->code . ']['.$p_id.']');
		$stat_obj->add(0, $resp->code, '', '', $client->appkey);
		if($resp->code == 7 && !$bak_channel){
			return taobaoItemDetail($p_id, true);
		}
		$info = array();
	}else{
		//无返利
		$stat_obj->add(1, 'no_rebate', $p_id, '', $client->appkey);
		$info = array();
	}

	return $info;
}

?>