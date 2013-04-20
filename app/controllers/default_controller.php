<?php

class DefaultController extends AppController {

	var $name = 'Default';
	var $uses = array('OrderFanli', 'StatJump', 'UserFanli');
	var $loginValide = false;
	var $layout = 'ajax';

	function up() {

		//15天以前被暂停的推手，如果账户无资产，则恢复身份并清空pause_date
		//如果有资产，则查询order判断购物金额是否大于30，如果小于30则重新恢复身份
		$weekdate = date('Y-m-d', time() - 12 * 24 * 3600);
		$weekdate2 = date('Y-m-d', time() - 26 * 24 * 3600);
		$this->UserFanli->query("UPDATE user_fanli SET status=1, pause_date='0000-00-00 00:00:00' WHERE role=3 AND status = 2 AND pause_date < '{$weekdate}' AND fl_fb=0");
		$users = $this->UserFanli->findAll("role=3 AND status = 2 AND pause_date < '{$weekdate}' AND pause_date > '{$weekdate2}' AND fl_fb>0");

		clearTableName($users);
		foreach ($users as $user) {

			if ($nu = $this->UserFanli->query("SELECT sum(p_price) as nu FROM order_fanli WHERE jumper_uid='{$user['userid']}'")) {
				$nu = @intval($nu[0][0]['nu']);
				if ($nu < 30) {
					$this->UserFanli->save(array('userid' => $user['userid'], 'status' => 1));
				}
			};
		}
	}

	function index($pass='') {

		if ($pass != 'pub')
			die();
		$i = 0;
		$fanli = 0;
		$global = array();
		$message = '';
		$new2 = array();

		$last = date('Y-m-d');
		$total_fanli = $this->OrderFanli->findSum('p_fanli', "payed=0 AND status=1 AND donedate<'{$last}'");
		$total_fanli_all = $this->OrderFanli->findSum('p_fanli', "payed=0");
		$total_fanli_orders = $this->OrderFanli->findCount("payed=0 AND status=1 AND donedate<'{$last}'");
		//提交了订单入库
		if (isset($_FILES['file'])) {
			$file = file_get_contents($_FILES["file"]["tmp_name"]);

			//去除UTF BOM头
			if (substr($file, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf)) {
				$file = substr($file, 3);
			}

			$datas = explode("\r\n", trim($file));
			if ($datas) {
				foreach ($datas as $data) {
					$d = explode("\t", trim($data));
					if (!isset($d[9])) {
						$message = 'file format error!';
					}
					else {
						$new = array();
						$new['did'] = $d[0];
						$new['p_id'] = $d[1];
						$new['status'] = $d[2];
						$new['ordernum'] = $d[3];
						$new['p_title'] = $d[4];
						$new['p_price'] = $d[5];
						$new['p_seller'] = $d[6];
						$new['p_yongjin'] = $d[7];
						$new['buydatetime'] = $d[8];
						$new['donedatetime'] = $d[9];
						$new['jumper_uid'] = $d[10];
						$new['p_fanli'] = $new['p_yongjin'] * C('config', 'RATE');
						$new['p_rate'] = C('config', 'RATE');
						$new['buydate'] = date('Y-m-d', strtotime($new['buydatetime']));
						$new['donedate'] = date('Y-m-d', strtotime($new['donedatetime']));
						//去除内部卖家
						if (in_array($new['p_seller'], C('config', 'HOLD_SELLER'))) {
							continue;
						}
						if (intval($new['did']) < 1 || intval($new['p_id']) < 1) {
							continue;
						}

						//关联jump记录
						$date_start = date('Y-m-d', strtotime($new['buydatetime']) - 24 * 3600);
						$date_end = date('Y-m-d', strtotime($new['buydatetime']) + 24 * 3600);
						$hit = $this->StatJump->find("p_id = {$new['p_id']} AND created>'{$date_start}' AND created<'{$date_end}'");

						if ($hit) {
							clearTableName($hit);
							$global[$new['ordernum']] = $hit['outcode'];
							$global_jumper[$new['jumper_uid']][$new['p_seller']] = $hit['outcode'];
						}

						$new2[] = $new;
					}
				}

				foreach ($new2 as $n) {

					if (isset($global[$n['ordernum']])) {
						$n['outcode'] = $global[$n['ordernum']];
					}
					else {
						if (isset($global_jumper[$n['jumper_uid']][$n['p_seller']])) {
							$n['outcode'] = $global_jumper[$n['jumper_uid']][$n['p_seller']];
						}
					}

					if ($n['outcode'] == 'test')
						continue;

					if (!$this->OrderFanli->find(array('did' => $n['did'], 'status' => $n['status']))) {

						if ($id = $this->OrderFanli->field('id', array('did' => $n['did']))) {
							$n['id'] = $id;
						}

						$this->OrderFanli->create();
						$this->OrderFanli->save($n);
						$fanli += $n['p_fanli'];
						$i++;
					}
				}

				$fanli = floatval($fanli);
				$message = "orders: {$i} fanli: {$fanli} rate: " . C('config', 'RATE') * 100 . "%";

				//优化推手，利益最大化
				//15天以前被暂停的推手，如果账户无资产，则恢复身份并清空pause_date
				//如果有资产，则查询order判断购物金额是否大于30，如果小于30则重新恢复身份
				$weekdate = date('Y-m-d', time() - 12 * 24 * 3600);
				$weekdate2 = date('Y-m-d', time() - 26 * 24 * 3600);
				$this->UserFanli->query("UPDATE user_fanli SET status=1, pause_date='0000-00-00 00:00:00' WHERE role=3 AND status = 2 AND pause_date < '{$weekdate}' AND fl_fb=0");
				$users = $this->UserFanli->findAll("role=3 AND status = 2 AND pause_date < '{$weekdate}' AND pause_date > '{$weekdate2}' AND fl_fb>0");

				clearTableName($users);
				foreach ($users as $user) {

					if ($nu = $this->UserFanli->query("SELECT sum(p_price) as nu FROM order_fanli WHERE jumper_uid='{$user['userid']}'")) {
						$nu = @intval($nu[0][0]['nu']);
						if ($nu < 30) {
							$this->UserFanli->save(array('userid' => $user['userid'], 'status' => 1));
						}
					};
				}
			}
			else {
				$message = 'file format error!';
			}
		}
		$this->set('message', $message);
		$this->set('total_fanli', $total_fanli);
		$this->set('total_fanli_orders', $total_fanli_orders);
		$this->set('total_fanli_all', $total_fanli_all);
	}

	function pay(){

		$last = date('Y-m-d');
		$orders = $this->OrderFanli->findAll("payed=0 AND status=1 AND donedate<'{$last}'");
		clearTableName($orders);
		$i = 0;
		$fanli = 0;
		foreach($orders as $order){
			$this->OrderFanli->save(array('id'=>$order['id'], 'payed'=>1, 'payed_date'=>$last));
			$fanli += $order['p_fanli'];
			$i++;
		}

		echo "<script>alert('payed {$i} orders {$fanli}');window.location.href='/Default/index/pub'</script>";
		die();
	}

}

?>