<?php

require_once APP ."controllers/api_jump_controller.php";

class ApiJumpFanliController extends ApiJumpController {

	var $name = 'ApiJumpFanli';

	/**
	 * 默认使用返利网，记录到跳转日志以便跟单对应，选择跟单用户并跳转出去
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

					$this->_addStatJump($shop, 'fanli', $my_user, $oc, $userid);
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

		$this->_addStatJump($shop, 'fanli', $my_user, $oc, $user['userid'], $p_id, $p_title, $p_price, $p_fanli, $p_seller);

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

}

?>