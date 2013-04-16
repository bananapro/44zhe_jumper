<?php

class CronController extends AppController {

	var $name = 'Cron';
	var $uses = array('UserFanli', 'OrderFanli', 'UserMizhe');

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
			$weekdate = date('Y-m-d', time() - 15 * 24 * 3600);
			$daydiff = date('Y-m-d', time() - 7 * 24 * 3600);
			//新建用户必须满7天以上才需要同步资产
			$users = $this->UserFanli->findAll("(role IN(1,2) AND status IN(1,3) AND created < '{$daydiff}') OR (status = 2 AND pause_date > '{$weekdate}')");
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

			$users = $this->UserMizhe->findAll();
			clearTableName($users);

			require_once MYLIBS . 'html_dom.class.php';

			foreach ($users as $user) {
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

							if($cash || $cash_history){
								$succ = true;
								$this->UserMizhe->save(array('userid'=>$user['userid'], 'cash'=>$cash, 'cash_history'=>$cash_history));
								echo "{$user['userid']} cash:{$cash} cash_history: {$cash_history}";
								br();
							}
						}
					}
				}

				if(!$succ){
					$succ = false;
					echo "{$user['userid']} cash update error!";
					br();
				}
			}

			echo 'done';
			die();
		}
	}

	//每月执行一次，更新10位推荐人
	function updateRecommender($n = 10) {

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
			$num[$a['area']] = ceil($n * $a['nu'] / $total);
		}

		foreach ($num as $area => $n) {

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
		//账户被停用后仍然继续跟单21天
		$page_size = 100;
		$weekdate = date('Y-m-d', time() - 21 * 24 * 3600);
		$users = $this->UserFanli->findAll("(role IN(1,2) AND status IN(1,3)) OR (status = 2 AND pause_date > '{$weekdate}')");
		$userids = fieldSet($users, 'userid');
//对用户进行分段，每30个一组
		$page = ceil(count($userids) / $page_size);

		$sql = <<<ETO
select [编号] as id, num_iid,fanlistate,ordernum_parent,productnum,productprice,seller_nick,yongjin,buydate,[dingdan].inputdate,memberzhanghao from [51fanli].[dbo].[dingdan]  left join
[51fanli].[dbo].[FL_ods] on [编号] = did
where s_id='712' and memberzhanghao IN({userid}) and [编号] > {max_id}
ETO;

		$sql_change_status = <<<ETO
select [编号] as id, num_iid,fanlistate,ordernum_parent,productnum,productprice,seller_nick,yongjin,buydate,[dingdan].inputdate,memberzhanghao from [51fanli].[dbo].[dingdan]  left join
[51fanli].[dbo].[FL_ods] on [编号] = did
where s_id='712' AND [编号] IN ({did})
ETO;

		$sql_arr = array();

		$max_did = $this->OrderFanli->find('', 'did', 'did desc');
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

		$orders = $this->OrderFanli->findAll(array('status' => array(2, 3, 4, 5)));
		$dids = fieldSet($orders, 'did');
		$dids = join($dids, ',');

		if ($dids) {
			$sql_change_status = str_replace('{did}', $dids, $sql_change_status);
			br(2);
			echo $sql_change_status;
		}
		die();
	}

}

?>