<?php

class CronController extends AppController {

	var $name = 'Cron';
	var $uses = array('UserFanli', 'OrderFanli', 'UserMizhe', 'StatJump');

	//每日执行一次，更新用户的ID
	function updateUserid($type = 'fanli') {

		if ($type == 'fanli') {
			$all_users = $this->UserFanli->findAll(array('userid' => 0, 'status' => 0));
			clearTableName($all_users);
			foreach ($all_users as $u) {
				$p = array();
				$p['username'] = $u['username'];
				$p['type'] = 2;
				$data = file_get_contents(ApiFanliPassport('/api/admin/getNameOrId', $p));
				$data = json_decode($data, true);
				if ($data['status'] == 1 && $data['data']) {
					$this->UserFanli->query("Update user_fanli SET userid={$data['data']}, status=1 WHERE username='{$u['username']}'");
					echo "{$u['username']} ==> {$data['data']}";
					br();
				}
				else {
					echo "{$u['username']} ==> error!";
					br();
				}
			}
		}
		echo 'done!';
		br();
		die();
	}

	//每日执行一次，更新用户返利网的资产
	function updateUserFanli($type = 'fanli') {

		if ($type == 'fanli') {

			//$users = $this->UserFanli->findAll(array('role' => array(1, 2, 3), 'status' => array(1, 3)));
			$weekdate = date('Y-m-d', time() - 60 * 24 * 3600);
			$daydiff = date('Y-m-d', time() - 3 * 24 * 3600);
			//新建用户必须满3天以上才需要同步资产
			$users = $this->UserFanli->findAll("(role IN(1,2,4) AND status IN(1,3) AND created < '{$daydiff}') OR (status = 2 AND pause_date > '{$weekdate}')");
			echo "update " . count($users) . " users";
			br(2);
			$userids = fieldSet($users, 'userid');
			//对用户进行分段，每50个一组
			$page = ceil(count($userids) / 30);
			for ($i = 0; $i < $page; $i++) {
				$new = array_slice($userids, $i * 30, 30);
				$ids = join($new, ',');
				$p = array();
				$p['userid'] = $ids;

				$data = file_get_contents(ApiFanliPassport('/api/admin/userAccountBalance', $p));
				$data = json_decode($data, true);
				if ($data['status'] == 1 && $data['data']) {
					foreach ($data['data'] as $userid => $info) {
						$this->UserFanli->save(array('userid' => $userid, 'fl_cash' => $info['fanli_yuan'], 'fl_fb' => $info['jifen']));
						$role = searchArray($users, 'userid', $userid, 'role');

						if (!$info['fanli_yuan'] && !$info['jifen'])
							continue;
						echo "{$userid}:{$role} => cash:<b>{$info['fanli_yuan']}</b> FB:{$info['jifen']}";

						br();
					}
				}
			}

			br();
			echo 'done!';
			die();
		}

		if ($type == 'mizhe') {

			$users = $this->UserMizhe->findAll(array('status' => array(0, 1)));
			clearTableName($users);

			require_once MYLIBS . 'html_dom.class.php';
			foreach ($users as $user) {

				//防止一个session内重复更新
				if (@$_SESSION['mizhe_update'][$user['userid']]) {
					echo "{$user['userid']} updated this session";
					br(2);
					continue;
				}

				$succ = false;
				$curl = mizheLogin($user['userid'], true);
				if ($curl) {
					$i = $curl->get('http://i.mizhe.com/');
					if ($i) {
						$html = new simple_html_dom($i);
						$dom = $html->find('span[class=green-price] em', 0);
						if ($dom) {

							$cash = 0;
							$cash = $dom->text();

							$dom = $html->find('span[class=price] em', 0);
							$cash_history = 0;
							$cash_history = $dom->text();

							$_SESSION['mizhe_update'][$user['userid']] = true;
							if ($cash || $cash_history) {
								$succ = true;
								$this->updateMizheOrder($user['userid'], $curl);
								$this->UserMizhe->save(array('userid' => $user['userid'], 'cash' => $cash, 'cash_history' => $cash_history));

								echo "{$user['userid']} cash:{$cash} cash_history: {$cash_history}";
								br(2);
							}
						}
					}
				}

				if (!$succ) {
					//更新不成功则应重新换代理
					$succ = false;
					unset($_SESSION['mizhe_login_proxy'][$user['userid']]);
					echo "{$user['userid']} cash update error!";
					br(2);
				}
			}

			echo 'done';
			die();
		}
	}

	function postMizheOrder(){

		if (isset($_FILES['file'])) {
			$file = file_get_contents($_FILES["file"]["tmp_name"]);
			$userid = $_POST['userid'];
			if(!$file || !$userid){
				die('please input userid & file');
			}

			require_once MYLIBS . 'html_dom.class.php';
			$this->updateMizheOrder($userid, null, $file);
		}
		die();
	}

	function updateMizheOrder($userid, $curl, $content='') {

		if ($content)
			$i = $content;
		else
			$i = $curl->get('http://i.mizhe.com/order/income.html');

		if ($i) {
			$html = new simple_html_dom($i);
			$doms = $html->find('ul[class=order-list-main] li');
			$new = array();
			foreach ($doms as $dom) {
				$link = $dom->find('a', 0);
				if ($link) {
					$link = $link->href;
					$return = preg_match('/([0-9]+)/i', $link, $matches);
					if ($return) {
						$order = array();
						$order['p_id'] = $matches[1];
						$p_title = $dom->find('div[class=title] a', 0);
						$order['p_title'] = $p_title->text();

						$num = $dom->find('div[class=title] p', 0);
						if (preg_match('/([0-9]+)件/i', $num->text(), $matches)) {
							$order['num'] = intval($matches[1]); //可能存在同一订单多个
						}

						$seller = $dom->find('div[class=title] p[class=clearfix] a', 0);
						$order['p_seller'] = $seller->text();

						$ordernum = $dom->find('div[class=date] b', 0);
						$order['ordernum'] = $ordernum->text();

						$donedate = $dom->find('div[class=date] p', 1);
						$order['donedate'] = $donedate->text();
						$order['donedatetime'] = $donedate->text();

						//下单日期反推10天
						$order['buydate'] = date('Y-m-d', strtotime($order['donedate']) - 10 * 24 * 3600);
						$order['buydatetime'] = date('Y-m-d H:i:s', strtotime($order['donedatetime']) - 10 * 24 * 3600);

						$y = md5($order['p_title']);
						$order['did'] = '10' . strtotime($order['donedatetime']) . hexdec($y[1] . $y[2]);

						$p_price = $dom->find('div[class=price] em', 0);
						$order['p_price'] = $p_price->text();

						$p_yongjin = $dom->find('div[class=rebate] em', 0);
						$p_yongjin = $p_yongjin->text();
						$order['p_yongjin'] = $p_yongjin * 100 / C('config', 'MIZHE_RATE'); //米折网折扣
						$order['p_fanli'] = $order['p_yongjin'] * C('config', 'RATE');

						$order['p_rate'] = C('config', 'RATE');
						$order['jumper_uid'] = $userid;
						//去除内部卖家
						if (in_array($order['p_seller'], C('config', 'HOLD_SELLER'))) {
							continue;
						}

						$order['type'] = 2;

						//如果能正常访问到页面，但解析错误，报警
						if ($order['p_price'] < 1 || !$order['p_title']) {
							alert('rsync mizhe order', 'userid : ' . $userid . ' error');
							continue;
						}

						//关联jump记录
						$date_start = date('Y-m-d', strtotime($order['donedatetime']) - 12 * 24 * 3600);
						$hit = $this->StatJump->find("p_id = {$order['p_id']} AND created>'{$date_start}'");

						if (!$hit) {
							$hit = $this->StatJump->find("p_seller = '{$order['p_seller']}' AND created>'{$date_start}'");
						}

						if ($hit) {
							clearTableName($hit);
							$global[$order['ordernum']] = $hit['outcode'];
							$global_jumper[$hit['jumper_uid']][$order['p_seller']] = $hit['outcode'];
						}

						$new[] = $order;
					}
				}
			}

			$fanli = 0;
			$i = 0;
			foreach ($new as $n) {

				if (isset($global[$n['ordernum']])) {
					$n['outcode'] = $global[$n['ordernum']];
				}
				else {
					if (isset($global_jumper[$n['jumper_uid']][$n['p_seller']])) {
						$n['outcode'] = $global_jumper[$n['jumper_uid']][$n['p_seller']];
					}
				}

				if (@$n['outcode'] == 'test')
					continue;

				if ($this->OrderFanli->find(array('did' => $n['did'])))
					continue;

				$this->OrderFanli->create();
				$this->OrderFanli->save($n);
				$fanli += $n['p_fanli'];
				$i++;
			}
			$fanli = intval($fanli);
			$message = "{$userid} orders: {$i} fanli: {$fanli} rate: " . C('config', 'RATE') * 100 . "%";
			echo $message;
			br();
		}
	}

	//每月执行一次，更新10位推荐人
	function updateRecommender($n = 20) {

		$date = date('Y-m-d H:i:s');
		$this->UserFanli->query("UPDATE user_fanli SET status=2, pause_date='" . $date . "'  WHERE status=1 AND role=2");

		for ($i = 1; $i <= $n; $i++) {
			//取出干净的会员
			$u = $this->UserFanli->find(array('role' => 0, 'status' => 1), '', 'rand()');
			if ($u) {
				clearTableName($u);
				$this->UserFanli->save(array('userid' => $u['userid'], 'role' => 2));
				echo "[{$u['area']}] {$u['userid']} become recommender";
			}
			else {
				echo "can not find a clear user";
			}

			br();
		}

		echo 'done!';
		br();
		die();
	}

	//每周执行一次，从被推池按地区均匀抽出10人作为大池
	function updateBig($n = 10) {

		$date = date('Y-m-d H:i:s');
		$this->UserFanli->query("UPDATE user_fanli SET status=2, pause_date='" . $date . "' WHERE status=1 AND role=1");

		$date = date('Y-m-d', time() - 30 * 24 * 3600);
		$area = $this->UserFanli->query("SELECT count(*) nu, area FROM user_fanli WHERE created>'{$date}' GROUP BY area");
		clearTableName($area);
		$total = 0;
		foreach ($area as $a) {
			$total += $a['nu'];
		}

		$num = array();
		foreach ($area as $a) {
			$num[$a['area']] = floor($n * $a['nu'] / $total);
		}

		foreach ($num as $area => $n) {

			if(!$n)continue;
			for ($i = 1; $i <= $n; $i++) {
				//取出干净的被推会员
				$u = $this->UserFanli->getPoolSpan($area);
				if ($u) {
					$this->UserFanli->save(array('userid' => $u['userid'], 'role' => 1));
					echo "[{$area}] {$u['userid']} become big";
				}
				else {
					echo "[{$area}] can not find a span user";
				}

				br();
			}
		}

		die();
	}

	//生成同步订单的SQL，执行完后保存到本地，上传到对账首页
	function createRsyncOrderSql($target='51fanli') {

		$sql = <<<ETO
select [编号] as id,num_iid,fanlistate,ordernum_parent,productnum,productprice,seller_nick,yongjin,buydate,[dingdan].inputdate,memberzhanghao,[dingdan].fanli,s_id from [51fanli].[dbo].[dingdan]  left join
[51fanli].[dbo].[FL_ods] on [编号] = did
where memberzhanghao IN({userid}) and [编号] > {max_id}
ETO;

		$sql_shop = <<<ETO
select distinct [编号] as id,num_iid,fanlistate,ordernum,productnum,productprice,seller_nick,yongjin,buydate,[dingdan].inputdate,memberzhanghao,[dingdan].fanli,s_id from [51fanli].[dbo].[dingdan]  left join
[51fanli].[dbo].[FL_ods] on [编号] = did
where memberzhanghao IN({userid}) and [编号] > {max_id}
ETO;

		$sql_change_status = <<<ETO
select [编号] as id,num_iid,fanlistate,ordernum_parent,productnum,productprice,seller_nick,yongjin,buydate,[dingdan].inputdate,memberzhanghao,[dingdan].fanli,s_id from [51fanli].[dbo].[dingdan]  left join
[51fanli].[dbo].[FL_ods] on [编号] = did
where [编号] IN ({did})
ETO;


		//计算淘宝订单
		//账户被停用后仍然继续跟单21天
		$page_size = 100;
		$weekdate = date('Y-m-d', time() - 21 * 24 * 3600);
		$users = $this->UserFanli->findAll("(role IN(1,2) AND status IN(1,3)) OR (status = 2 AND pause_date > '{$weekdate}')");
		$userids = fieldSet($users, 'userid');
//对用户进行分段，每30个一组
		$page = ceil(count($userids) / $page_size);

		$sql_arr = array();

		$max_did = $this->OrderFanli->find(array('type' => 1), 'did', 'did desc');
		clearTableName($max_did);
		$max_did = intval($max_did['did']);
		if (!$max_did)
			$max_did = '70273945';
		for ($i = 0; $i < $page; $i++) {
			$new = array_slice($userids, $i * $page_size, $page_size);
			$ids = join($new, ',');
			$sql_arr[] = str_replace(array('{userid}', '{max_id}'), array($ids, $max_did), $sql);
		}

		echo join(";<br /><br />", $sql_arr);

		//计算商城订单
		$users = $this->UserFanli->findAll("role = 4 AND (status IN(1,3) OR (status = 2 AND pause_date > '{$weekdate}'))");
		$userids = fieldSet($users, 'userid');
		$ids = join($userids, ',');
		$sql_shop = str_replace(array('{userid}', '{max_id}'), array($ids, $max_did), $sql_shop);
		br(2);
		echo $sql_shop;

		//计算change status
		$orders = $this->OrderFanli->findAll(array('status' => array(3, 4)));
		$dids = fieldSet($orders, 'did');
		$dids = join($dids, ',');

		if ($dids) {
			$sql_change_status = str_replace('{did}', $dids, $sql_change_status);
			br(2);
			echo $sql_change_status;
		}
		die();
	}


	/**
	 * 提取返利网现金步骤
	 * 1、上传autocash_pay.php脚本至73
	 * 2、修改73的passport host到192
	 * 3、修改Action/admin/autoCashAlipayJifen  注释返利升级用户等级代码
	 * 4、修改提现目标的支付宝
	 * 5、依次请求该方法生成的语句
	 */
	function createDuixianScript($all=null){

		$month = date('Y-m-d', time() - 60*24*3600);
		if(!$all){
			$users = $this->UserFanli->findAll("status=2 AND pause_date<'{$month}' AND ((role=1 AND fl_fb>10000) OR (role=3 AND fl_fb>0)) AND alipay<>''");
		}else{
			$users = $this->UserFanli->findAll("status=2 AND ((role=1 AND fl_fb>10000) OR (role=3 AND fl_fb>0)) AND alipay<>''");
		}

		clearTableName($users);
		$userids = array();
		foreach($users as $user){
			$api = ApiFanliPassport('/api/admin/updateBank', array('userid'=>$user['userid'], 'pay_method'=>2, 'pay_account'=>$user['alipay'], 'ip'=>'127.0.0.1'));
			echo "wget '$api'";
			echo "<br />";
			$userids[] = $user['userid'];
		}

		echo "<br />";
		echo "<br />";
		echo "<br />";

		foreach($users as $user){
			echo "/usr/local/php5/bin/php autocash_pay.php {$user['userid']}<br />";
		}

		echo "<br />";
		echo "<br />";
		echo "<br />";

		$ids = join(',', $userids);
		//echo "DELETE FROM 51fanli_passport.tb_pay_account WHERE userid IN ($ids) AND pay_method=2";

		//echo "<br />";
		//echo "<br />";

		echo "UPDATE  `user_fanli` SET fl_fb =0 WHERE userid IN ($ids)";
		die();
	}
}

?>