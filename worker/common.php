<?PHP

define('DS', '/');
define('ROOT', dirname(__FILE__) . DS);
define('API', 'http://www.jumper.com/api/');
define('API_DUOSQ', 'http://api.duo.com/');
define('CACHE', ROOT . 'tmp' . DS);
define('COOKIE', CACHE . 'login' . DS); //登陆login保存路径

if(is_dir(ROOT . '../../cake/mylibs/'))
	define('MYLIBS', ROOT . '../../cake/mylibs/');
else
	define('MYLIBS', ROOT . '../cake/mylibs/');

if (@$_GET['debug']) {
	error_reporting(E_ALL & ~E_DEPRECATED);
	ini_set('display_errors', 1);
}

require MYLIBS . 'curl.class.php';
require MYLIBS . 'tran_proxy.class.php';
require MYLIBS . '../basics.php';
require ROOT . '../app/config/bootstrap.php';

$curl = new CURL();
$proxy = new tranProxy();

function requestApi($api) {

	if (stripos($api, '?') != false) {
		$data = file_get_contents(API . $api . '&debug=false');
	}
	else {
		$data = file_get_contents(API . $api . '?debug=false');
	}

	if ($data) {
		$data = json_decode($data, true);
		if ($data['status'] == 1) {
			return $data['message'];
		}
	}
	return false;
}

function requestApiDuosq($api){

	if (stripos($api, '?') != false) {
		$data = file_get_contents(API_DUOSQ . $api . '&debug=false');
	}
	else {
		$data = file_get_contents(API_DUOSQ . $api . '?debug=false');
	}

	if ($data && $data != 'empty') {
		$data = unserialize($data);
		if(is_array($data)){
			return $data;
		}
	}
	return false;
}

/**
 * 领取worker任务
 * @return array
 */
function getTask() {
	return requestApi('getWorkerTask');
}

/**
 * worker完成任务后回传状态
 * @param int $taskid
 * @param int $status
 * @return type
 */
function finishTask($taskid, $status, $msg='') {
	return requestApi('finishWorkerTask/' . $taskid . '/' . $status . '?msg=' . urlencode($msg));
}

/**
 * 获取所有待完成任务个数
 * @return type
 */
function getTaskTotal() {
	return requestApi('getWorkerTaskTotal');
}

/**
 * 获取跳转用户信息
 * @param type $type
 * @param type $uid
 * @return type
 */
function getJumperInfo($type, $uid) {
	return requestApi('getJumperInfo/' . $type . '/' . $uid);
}

/**
 * 登陆失败时做本地记录
 * @param type $type
 * @param type $uid
 */
function loginFail($type, $uid) {
	file_put_contents(COOKIE . $type . '/' . $uid . '.cookie', '');
}

/**
 * 登陆成功时清除本地失败标记
 * @param type $type
 * @param type $uid
 */
function loginSucc($type, $uid, $cookies = '') {

	$expires = time() + 365 * 86400;
	if (!$cookies) {
		//把cookie有效时间设为永久
		$cookies = file_get_contents(COOKIE . $type . DS . $uid . '.cookie');
		$cookies = preg_replace('/#.*?\n/', '', $cookies);
		$cookies = trim(str_replace("\n\n", "\n", $cookies));
		if ($cookies) {
			$lines = explode("\n", $cookies);
			$e = array();
			foreach ($lines as $l) {
				if (trim($l)) {
					$tmp = explode("\t", $l);
					if($tmp[6] == 'deleted')continue;
					$tmp[0] = ".{$type}.com";
					$tmp[1] = "TRUE";
					$tmp[2] = "/";
					$tmp[4] = $expires;
					$e[] = join("\t", $tmp);
				}
			}
			$e = join("\n", $e);
		}
	}
	else {
		$cookie_str = '';
		$e = '';
		$cookie_name = array();
		foreach ($cookies as $c) {
			if($c['value'] == 'deleted')continue;
			$e .= ".{$type}.com\tTRUE\t/\tFALSE\t{$expires}\t{$c['name']}\t{$c['value']}\n";
			$cookie_name[$c['name']] = 1;
		}
		foreach($_COOKIE as $key => $value){
			if(isset($cookie_name[$key]))continue;
			$e .= ".{$type}.com\tTRUE\t/\tFALSE\t{$expires}\t{$key}\t{$value}\n";
		}
		//合并当前COOKIE，防止第一步浏览器得到了session，save时遗漏
	}

	$e = trim($e);
	if($e){
		file_put_contents(COOKIE . $type . DS . $uid . '.cookie', $e);
		return true;
	}
}

/**
 * 缓存静态资源(css/js/img)
 * @param type $url
 * @return type
 */
function getCacheStatic($url) {

	global $proxy;
	$md5 = md5($url);
	$path = "static" . DS . "{$md5[0]}" . DS . $md5;
	$static_cache = cache($path, null, 86400);
	if (!$static_cache) {
		$static_cache = $proxy->request($url);
		//去除UTF BOM头
		if (substr($static_cache, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf)) {
			$static_cache = substr($static_cache, 3);
		}
		$is_static = false;
		if (preg_match('/(.jpg|.gif|.bmp|.png|.js|.css|.ico)/i', $url)) {
			$_SESSION['shutdown_debug'] = true;
			$is_static = true;
		}

		if ($static_cache && $is_static)
			cache($path, $proxy->response_headers . "{||}" . $static_cache);
	}else {

		//恢复缓存的头部
		list($proxy->response_headers, $static_cache) = explode("{||}", $static_cache);
		$proxy->return_response(true);
	}
	return $static_cache;
}

/**
 * 获取本地所有登陆失败记录
 */
function getLoginFailCookies() {

	$hit = array();
	$dir_type = scandir(COOKIE);
	foreach ($dir_type as $type) {
		if ($type != '.' && $type != '..') {
			$dir_cookie = scandir(COOKIE . $type);
			foreach ($dir_cookie as $name) {
				if ($name != '.' && $name != '..') {
					$cookies = file_get_contents(COOKIE . $type . DS . $name);
					$cookies = preg_replace('/#.*?\n/', '', $cookies);
					$cookies = trim(str_replace("\n\n", "\n", $cookies));
					if ($cookies) {
						continue;
					}
					$hit[$type][str_replace('.cookie', '', $name)] = 1;
				}
			}
		}
	}
	return $hit;
}

/**
 * 透明代理获取任务信息
 */
function proxyGetMission() {

	//摘掉任务，做好cookie准备，并清空痕迹防止第三方接收到
	$e = array();
	if (isset($_GET['carry_mission'])) {

		list($e['mission_type'], $e['jumper_type'], $e['jumper_uid']) = explode(':', $_GET['carry_mission']);

		if ($e) {
			setcookie("carry_mission", $_GET['carry_mission'], 0, '/'); //存储到cookie，登陆后可以继续取得任务
		}
		unset($_GET['carry_mission']);

		//任务开始，清空上次的cookie
		foreach ($_COOKIE as $k => $v) {
			if ($k != 'carry_mission') {
				unset($_COOKIE[$k]);
				setcookie($k, '', -1000000, '/', ".{$e['jumper_type']}.com");
				//setcookie($k, '', -1000000, '/', "www.{$e['jumper_type']}.com"); 防止header过大，页面502
			}
		}
	}
	else if (isset($_COOKIE['carry_mission'])) {

		list($e['mission_type'], $e['jumper_type'], $e['jumper_uid']) = explode(':', $_COOKIE['carry_mission']);
		unset($_COOKIE['carry_mission']);
	}
	else if ($file = @file_get_contents('/tmp/current_mission')){ //用于类似淘粉8之类的需联合登陆的情况传递任务

		list($e['mission_type'], $e['jumper_type'], $e['jumper_uid']) = explode(':', $file);
		unlink('/tmp/current_mission');
	}

	if ($e) {
		//缓冲用户信息
		$cache_path = 'jumper_info/' . $e['jumper_type'] . '/' . $e['jumper_uid'];
		$jumper_info = cache($cache_path, null, 86400);
		if (!$jumper_info) {
			$jumper_info = getJumperInfo($e['jumper_type'], $e['jumper_uid']);
			if ($jumper_info) {
				cache($cache_path, serialize($jumper_info));
			}
			else {
				return false;
			}
		}else{
			$jumper_info = unserialize($jumper_info);
		}

		$e = array_merge($e, $jumper_info);
	}

	return $e;
}

?>
