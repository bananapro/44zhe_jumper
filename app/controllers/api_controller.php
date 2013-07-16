<?php

class ApiController extends AppController {

	var $name = 'Api';
	var $uses = array('UserFanli', 'UserMizhe', 'UserCandidate', 'StatJump', 'StatRegFailed', 'StatJump', 'OrderFanli');
	var $layout = 'ajax';

	function demo() {
		$user = $this->UserFanli->getPoolSpan('', 2);
		pr($user);
		die();
	}

	function demoReg() {

	}

	function demoJump($driver='51fanli') {
		$this->set('driver', $driver);
	}

	function alert($target, $info) {

		alert($target, $info);
		if (@$_GET['u']) {
			$this->redirect($_GET['u']);
		}
		else {
			$this->redirect(DEFAULT_ERROR_URL);
		}
	}

	/**
	 * 判断是否有注册任务，如果有，则返回注册url
	 */
	function redirectRegUrl() {

		if (!C('config', 'ENABLE_REG'))
			$this->error();

		$user = $this->UserCandidate->find(array('is_used' => 0));

		//不允许当天IP领取重复的注册任务
		$reg_before = $this->UserCandidate->find("ip = '" . getip() . "' AND ts > '" . date('Y-m-d') . "'", 'id');

		if (!$user) {
			//报警，候选人库不足
			alert('user_candidate', 'empty');
		}

		if ($user && !$reg_before && array_search(getAreaByIp(), C('config', 'REG_EXCLUDE_AREA')) === false) {
			clearTableName($user);

			$rand = rand(3000, 8000);
			$username = $user['username'];
			$email = $user['email'];
			$password = $user['username'] . '0a';

			if ($rand > 500 * date('h')) {
				//让注册时间更加随即，早上6点到下午4点注册几率越来越大
				//$this->_error('reg task not luck');
			}

			//先完成普通注册任务
			if (!overlimit_day('REG_COMMON_PRE_DAY_LIMIT')) {

				$_SESSION['reg_username'] = $user['username'];
				$_SESSION['reg_email'] = $user['email'];
				$_SESSION['reg_parent'] = '';
				$fanli_reg_url = "http://passport.51fanli.com/Reg/ajaxUserReg?jsoncallback=jQuery17203368097049601636_1363270{$rand}&useremail={$email}&username={$username}&userpassword={$password}&userpassword2={$password}&skey=&regurl=http://passport.51fanli.com/reg?action=yes&refurl=&t=" . time() . "&_=136398{$rand}";
				$this->_success($fanli_reg_url);
			}
			else {
				//完成推荐注册任务
				if (!overlimit_day('REG_RECOMM_PRE_DAY_LIMIT')) {

					//recommenduid  recommendt
					$parent_data = $this->UserFanli->getPoolRecommender();
					if ($parent_data) {
						clearTableName($parent_data);
						$parent = $parent_data['username'];
						$_SESSION['reg_username'] = $user['username'];
						$_SESSION['reg_email'] = $user['email'];
						$_SESSION['reg_parent'] = $parent;
						$fanli_reg_url = "http://passport.51fanli.com/Reg/ajaxUserReg?jsoncallback=jQuery17203368097049601636_1363270{$rand}&useremail={$email}&username={$username}&userpassword={$password}&userpassword2={$password}&skey=&recommendid2={$parent}&recommendt=4&regurl=http://passport.51fanli.com/reg?action=yes&refurl=&t=" . time() . "&_=136398{$rand}";
						$this->_success($fanli_reg_url);
					}
					else {
						//报警，推池不足
						alert('recommender', 'empty');
					}
				}
			}
			unset($_SESSION['reg_username']);
			unset($_SESSION['reg_email']);
			unset($_SESSION['reg_parent']);
			//注册任务全部完成
			$this->_error('reg task complete');
		}
		else {

			//记录失败的注册请求
			$this->StatRegFailed->create();
			$this->StatRegFailed->save(array('ip' => getip(), 'area' => getAreaByIp(), 'date' => date('Y-m-d')));
			$this->_error('can not find user candidate');
		}
	}

	/**
	 * 注册成功后保存注册信息
	 */
	function jsonpRecordRegInfo($status) {

		if ($status) {
			if ($_SESSION['reg_username']) {
				$this->UserCandidate->query("UPDATE user_candidate SET is_used=1, `status`='{$status}', ip='" . getip() . "', area='" . getAreaByIp() . "' WHERE username='{$_SESSION['reg_username']}'");
				$this->UserFanli->create();

				//注册用户成功 status is 10000
				if ($status == '10000') {
					$user = array();
					$user['ip'] = getip();
					$user['area'] = getAreaByIp();
					$user['username'] = $_SESSION['reg_username'];
					$user['email'] = $_SESSION['reg_email'];
					$user['parent'] = @$_SESSION['reg_parent'];
					if ($user['parent'])
						$user['role'] = 3; //被推注册默认就有角色
					$this->UserFanli->save($user);

					//注册任务计数器计数
					if (!$user['parent'])
						overlimit_day_incr('REG_COMMON_PRE_DAY_LIMIT');
					else
						overlimit_day_incr('REG_RECOMM_PRE_DAY_LIMIT');
				}
			}
		}
		unset($_SESSION['reg_username']);
		unset($_SESSION['reg_email']);
		unset($_SESSION['reg_parent']);
		$this->_success();
	}

	/**
	 * 返回跳转JS，用于获得指定返利网商品加密链接
	 */
	function getJumpUrlJs($shop, $my_user, $p_id='', $p_price='', $p_fanli='') {

		//TODO取代接口形式获取

		$default_url = $_GET['u'];
		$oc = $_GET['oc'];
		$su = $_GET['su'];
		$target = $_GET['target'];
		$driver = '';
		$_SESSION['source'] = $_GET['su'];

		//判断是否允许运作
		if ($shop && $my_user && C('config', 'ENABLE_JUMP')) {
			$this->set('pass', true);
		}
		else {
			$this->set('pass', false);
		}

		$driver = $_GET['driver'];

		//淘宝以外流量暂时只能走返利网
		//劫持流量只能走返利网
		if ($shop != 'taobao' || $_GET['su'] == 1) {
			$driver = '51fanli';
		}

		$today = date('Y-m-d');
		$total = $this->StatJump->findCount("created>'" . $today . "' AND outcode<>'test'");
		$curr = $this->StatJump->findCount("created>'" . $today . "' AND outcode<>'test'  AND jumper_type='mizhe'");

		//大于2元返利，进行mizhe随机分派
		if (!$driver && $p_fanli>2) {

			//如果有mizhe成功跳转日志，且允许跳mizhe
			if($_COOKIE['mi_succ'] && C('config', 'JUMP_MIZHE_RATE')){
				$driver = 'mizhe';

			//如果随机分到mizhe(用于尝试米折通道)
			}else if(hitRate($total, $curr, C('config', 'JUMP_MIZHE_RATE'))){
				$driver = 'mizhe';
			}

			//尝试走过mizhe但没有成功过
			if ($_COOKIE['mi_try'] && !$_COOKIE['mi_succ']){
				$driver = false;
				alert('mizhe jump', '['. $oc .']['. getBrowser() .'] today failed');
			}

			//当认为走mizhe没问题时，容忍度降到0，也不再走mizhe
			if (isset($_COOKIE['mi_balance']) && $_COOKIE['mi_balance'] == 0){
				$driver = false;
				setcookie('mi_succ', 0, time() - 360 * 24 * 3600, '/'); //清除米折通道成功标识
				alert('mizhe jump', '['. $oc .']['. getBrowser() .'] balance become zero');
			}

			if ($driver == 'mizhe') {

				setcookie('mi_try', 1, time() + 3 * 24 * 3600, '/'); //每3天允许1次尝试mizhe

				$b = myisset($_COOKIE['mi_balance'], 3);
				$b--;
				if($b<= 0)$b = 0;
				setcookie('mi_balance', $b, time() + 7 * 24 * 3600, '/'); //mizhe失败容忍次数，减为0时7天不再走mizhe

				overlimit_day_incr('JUMP_MIZHE_FANLI_MAX', date('Ym'), $p_fanli);
			}
		}

		if(!$driver) $driver = '51fanli';

		$this->set('driver', $driver);
		$this->set('p_id', $p_id);
		$this->set('p_price', $p_price);
		$this->set('p_fanli', $p_fanli);
		$this->set('my_user', $my_user);
		$this->set('target', $target);
		$this->set('shop', $shop);
		$this->set('oc', $oc);
		$this->set('default_url', $default_url);
	}

	/**
	 * 客户端请求返利网接口出错时，强制进行s to s端的跳转
	 *
	 * http://go.44zhe.com/api/jumpForce/taobao/bluecone@163.com/18484876328/0.11/0.01
	 * @param type $shop
	 * @param type $my_user
	 * @param type $p_id
	 * @param type $p_price
	 * @param type $p_fanli
	 */
	function jumpForce($shop, $my_user, $p_id='', $p_price='', $p_fanli='') {

		$data = file_get_contents('http://fun.51fanli.com/api/search/getItemById?pid=' . $p_id . '&is_mobile=2&shoptype=2&track_code=a');
		if ($data) {
			$data = json_decode($data, true);
			if ($data['status']) {
				$_GET['ju'] = $data['data']['url'];
				$_GET['p_seller'] = $data['data']['shopname'];
				$_GET['p_title'] = $data['data']['title'];
			}
			else {
				alert('jumpForce', 'status error');
				$this->redirect(DEFAULT_ERROR_URL);
			}
		}
		else {
			alert('jumpForce', 'can not fetch data');
			$this->redirect(DEFAULT_ERROR_URL);
		}

		$this->jump($shop, $my_user, $p_id, $p_price, $p_fanli);
	}

	/**
	 * 使用返利网，记录到跳转日志以便跟单对应，选择跟单用户并跳转出去
	 * @param type $shopid
	 * @param type $my_user
	 * @param type $p_id
	 * @param type $p_title
	 * @param type $p_price
	 * @param type $p_fanli
	 */
	function jump($shop, $my_user, $p_id='', $p_price='', $p_fanli='') {

		$jump_url = @$_GET['ju'];
		$p_title = @$_GET['p_title'];
		$p_seller = @$_GET['p_seller'];
		$oc = $_GET['oc'];
		$shop = low($shop);
		$my_user = low(urldecode($my_user));
		$target = $_GET['target'];//商城页面

		//支持商城
		if ($shop != 'taobao') {

			require MYLIBS . 'jumper' . DS . 'rule_51fanli.class.php';

			//选出跳转userid
			$jump_rule = new rule($shop);

			if(C('shop_tpl', $shop)){
				$userid = $this->UserFanli->getShopUser($shop, $my_user);
				if($userid){
					$jump_url = $jump_rule->getUrl($userid, $target);

					$this->_addStatJump($shop, '51fanli', $my_user, $oc, $userid);
					$this->redirect('http://fun.51fanli.com/goshopapi/goout?' . time() . '&id=' . $jump_rule->ts['id'] . '&go=' . urlencode($jump_url) . '&fp=loading');
				}else{
					alert('shop jump', "[$shop][shop user not hit][$target]");
				}
			}else{
				alert('shop jump', "[$shop][$target]");
			}

			$this->redirect($_GET['target']);
		}

		if (preg_match('/go=(.+?)&tc/i', $jump_url, $match)) {
			$jump_url = $match[1];
		}
		else {
			if ($_GET['go']) {
				$jump_url = $_GET['go'];
				$jump_url = urlencode($jump_url);
			}
			else {
				//报警 跳转taobao的url遗失
				alert('FUN TaobaoKe Url', '[' . getip() . '][' . getBrowser() . '] can not find url');
				$this->redirect(DEFAULT_ERROR_URL);
			}
		}

		//改良算法，小于3元跳推荐，12天后无推荐成功恢复推手身份
		if ($p_fanli <= 3.5 && false) {

			if ($p_price >= 30) {

				//专门用来测试返利网模式跳转
				if ($my_user == 'bluecone@163.com' && getAreaByIp() == '上海') {
					$user = $this->UserFanli->getPoolSpan('辽宁');
				}
				else {
					$user = $this->UserFanli->getPoolSpan();
				}

				if (!$user) {//尽量找到
					$user = $this->UserFanli->getPoolSpan('辽宁');
					if (!$user)
						alert('Pool Error', 'level 1 empty');
				}
			}else {
				$user = $this->UserFanli->getPoolSpan('', 2);
				if (!$user) {//尽量找到
					$user = $this->UserFanli->getPoolSpan('辽宁', 2);
					if (!$user)
						alert('Pool Error', 'level 2 empty');
				}
			}

			if ($user) {
				//如果是被推池跳转则永久剔除
				$this->UserFanli->save(array('userid' => $user['userid'], 'status' => 2, 'pause_date' => date('Y-m-d H:i:s')));
			}
			else {
				if ($my_user == 'bluecone@163.com') {
					$this->redirect('/api/nojobs');
				}
			}
		}
		else {
			//跳入特殊账号，直到超过累计值
			$area = getAreaByIp();
			if (!overlimit_day('SP_FANLI_MAX', date('Ym'))) {
				$r = rand(0, 1); //每月1号以前几率递增
				if ($r < date('d')) {
					$hitSP = true;
					$user = array();
					$user['userid'] = C('config', 'SP_UID');
					overlimit_day_incr('SP_FANLI_MAX', date('Ym'), $p_fanli);
				}
			}
		}

		if (!@$user) {
			$user = $this->UserFanli->getPoolBig();
		}

		//没有跳转源
		if (!$user) {
			//使用辽宁用户做备胎并报警，此处是应缺少有关地区的大池用户
			$user = $this->UserFanli->getPoolBig('辽宁');
			//报警，找不到相应地区的大池用户
			//alert('Big pool', '[' . getAreaByIp() . '] can not found');
		}

		if (!$user) {
			alert('User Error', 'can not found a user for jump');
			$this->redirect(DEFAULT_ERROR_URL);
		}

		$this->_addStatJump($shop, '51fanli', $my_user, $oc, $user['userid'], $p_id, $p_title, $p_price, $p_fanli, $p_seller);

		//封装goshop跳转地址
		if($hitSP)
			$tc = 'ss'; //命中特殊额使用手机tc跳转赚4倍返利
		else
			$tc = null;
		$outcode = getOutCode($user['userid'], $tc);
		$jump_url = str_replace('$outcode$', $outcode, urldecode($jump_url));
		$jump_url = urlencode($jump_url);

		$tpl = C('shop_tpl', 'taobao');
		$this->redirect('http://fun.51fanli.com/goshopapi/goout?' . time() . '&id=' . $tpl['id'] . '&go=' . $jump_url . '&fp=loading');
	}

	/**
	 * 使用米折网，进行跳转跟单
	 * @param type $shopid
	 * @param type $my_user
	 * @param type $p_id
	 * @param type $p_title
	 * @param type $p_price
	 * @param type $p_fanli
	 */
	function jumpMizhe($shop, $my_user, $p_id='', $p_price='', $p_fanli='') {

		$oc = $_GET['oc'];
		$user = $this->UserMizhe->getUser();

		//种下mizhe标识符，下次跳转直接米折
		if($_COOKIE['mi_succ']){
			setcookie('mi_succ', 1, time() + 360 * 24 * 3600, '/'); //如果2次都跳mizhe成功则变成永久
		}else{
			setcookie('mi_succ', 1, time() + 1 * 24 * 3600, '/'); //1次成功只有效1天 - 防止有用户碰巧1次成功而已
		}

		//补偿mizhe错误容忍度
		$b = intval(@$_COOKIE['mi_balance']);
		$b = $b + 2;
		if($b <= 0)$b = 0;
		setcookie('mi_balance', $b, time() + 7 * 24 * 3600, '/'); //mizhe错误容忍次数，减为0时7天不再走mizhe

		if (!$user) {
			$this->jumpForce($shop, $my_user, $p_id, $p_price, $p_fanli);
		}

		//支持商城
		if ($shop != 'taobao') {
			if ($_GET['target']) {
				$this->redirect($_GET['target']);
			}
			else {
				$this->redirect(DEFAULT_ERROR_URL);
			}
		}

		//登陆后访问goshop链接会出现mm_27873525加密的elink
		//筛选出url、反解e参数、封装s.click直接跳转
		require_once MYLIBS . 'curl.class.php';
		require_once MYLIBS . 'html_dom.class.php';
		$curl = new CURL();
		$curl->follow = false;
		$curl->header = false;
		$curl->cookie_path = '/tmp/curl_cookie_' . C('config', 'MIZHE_DEFAULT_LOGIN_USERID') . '.txt';
		$dom = false;
		$page = $curl->get('http://go.mizhe.com/rebate/taobao/i-topot%E6%97%97%E8%88%B0%E5%BA%97-' . $p_id . '.html');
		//尝试获取米折网的淘宝客url
		if ($page) {
			$html = new simple_html_dom($page);
			$dom = $html->find('div[class=loading_onclick] a', 0);
			if (!$dom) {
				//登陆米折，该用户用于登陆
				if (!mizheLogin(C('config', 'MIZHE_DEFAULT_LOGIN_USERID'), false)) {
					$this->jumpForce($shop, $my_user, $p_id, $p_price, $p_fanli);
				}
				$page = $curl->get('http://go.mizhe.com/rebate/taobao/i-topot%E6%97%97%E8%88%B0%E5%BA%97-' . $p_id . '.html');
				$html = new simple_html_dom($page);
				$dom = $html->find('div[class=loading_onclick] a', 0);
			}
		}

		if (!$dom) {
			$this->jumpForce($shop, $my_user, $p_id, $p_price, $p_fanli);
		}

		$href = $dom->href;
		$href = str_replace('http://go.mizhe.com/r/', '', $href);
		$href = base64_decode(str_replace('_', '/', $href));
		$href = str_replace('tbkurl', '', $href);
		if (stripos($href, '/t?e=') === false) {
			$this->jumpForce($shop, $my_user, $p_id, $p_price, $p_fanli);
		}

		$href = str_replace('unid=1', 'unid=' . $user['userid'], $href);

		//TODO 用自己的key来获取
		$data = file_get_contents('http://fun.51fanli.com/api/search/getItemById?pid=' . $p_id . '&is_mobile=2&shoptype=2');
		if ($data) {
			$data = json_decode($data, true);
			$p_title = @$data['data']['title'];
			$p_seller = @$data['data']['shopname'];
		}

		$this->_addStatJump($shop, 'mizhe', $my_user, $oc, $user['userid'], $p_id, $p_title, $p_price, $p_fanli, $p_seller);

		$this->redirect('http://s.click.taobao.com' . $href);
	}


	/*
	 * 记录跳转日志
	 */
	function _addStatJump($shop, $jumper_type, $my_user, $outcode, $userid, $p_id='', $p_title='', $p_price='', $p_fanli='', $p_seller=''){
		//记录跳转日志
		$stat = array();
		$stat['p_id'] = $p_id;
		$stat['p_title'] = $p_title;
		$stat['p_price'] = $p_price;
		$stat['p_fanli'] = $p_fanli;
		$stat['p_seller'] = $p_seller;
		$stat['ip'] = getip();
		$stat['area'] = getAreaByIp();
		$stat['shop'] = $shop;
		$stat['jumper_uid'] = $userid;
		$stat['jumper_type'] = $jumper_type;
		$stat['my_user'] = urldecode($my_user);
		$stat['outcode'] = $outcode;
		$stat['target'] = $_GET['target'];
		$stat['client'] = getBrowser();
		$stat['source'] = $_SESSION['source'];

		foreach ($stat as $k => $v) {
			if (!$v)
				unset($stat[$k]);
		}

		$this->StatJump->create();
		$this->StatJump->save($stat);
	}

}

?>