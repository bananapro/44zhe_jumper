<?php

class DefaultController extends AppController {

	var $name = 'Default';
	var $uses = array('OrderFanli', 'StatJump', 'UserFanli', 'UserMizhe');
	var $loginValide = false;

	function index($pass='') {

		if ($pass != 'pub')
			die();
		$i = 0;
		$fanli = 0;
		$global = array();
		$global_jumper = array();
		$source = array();
		$source_jumper = array();
		$message = '';
		$new2 = array();
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
					$data = str_ireplace('NULL', '', $data);
					$d = explode("\t", trim($data));
					if (!isset($d[9]) || (!@$d[7] && !@$d[11])) {
						$message = 'file format error!<br />';
					} else {

						$shop = $d[12];
						if($shop == 's_id')continue;

						if($shop == 712){

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

							if (!$hit) {
								$hit = $this->StatJump->find("p_seller = '{$new['p_seller']}' AND created>'{$date_start}' AND created<'{$date_end}'");
							}

							if ($hit) {
								clearTableName($hit);
								$global[$new['ordernum']] = $hit['outcode'];
								$global_jumper[$new['jumper_uid']][$new['p_seller']] = $hit['outcode'];
								$source[$new['ordernum']] = $hit['source'];
								$source_jumper[$new['jumper_uid']][$new['p_seller']] = $hit['source'];
							}

						}else{

							$new = array();
							$new['did'] = $d[0];
							$new['p_id'] = $d[1];
							$new['status'] = $d[2];
							$new['ordernum'] = $d[3];
							$new['p_title'] = $d[4];
							$new['p_price'] = $d[5];
							$new['p_seller'] = $d[6];

							if(!$d[7]){//没有佣金字段，直接填充返利字段
								$new['p_yongjin'] = $d[11];
							}else{
								$new['p_yongjin'] = $d[7]*0.7;
							}

							$new['buydatetime'] = $d[8];
							$new['donedatetime'] = $d[9];
							$new['jumper_uid'] = $d[10];
							$new['p_fanli'] = $new['p_yongjin'] * C('config', 'RATE');
							$new['p_rate'] = C('config', 'RATE');
							$new['buydate'] = date('Y-m-d', strtotime($new['buydatetime']));
							$new['donedate'] = date('Y-m-d', strtotime($new['donedatetime']));

							//map 商城
							$shop_tpl = C('shop_tpl');
							foreach($shop_tpl as $shopname => $s){
								if($s['shopid'] == $shop){
									$new['shop'] = $shopname;
								}
							}

							if (intval($new['did']) < 1) {
								continue;
							}

							//关联jump记录
							$date_start = date('Y-m-d H:i:s', strtotime($new['buydatetime']) - C('config', 'SHOP_JUMP_DS_TIME'));
							$date_end = date('Y-m-d H:i:s', strtotime($new['buydatetime']) + 100);

							$hit = $this->StatJump->find("shop='".$new['shop']."' AND created>'{$date_start}' AND created<'{$date_end}' AND jumper_uid='{$new['jumper_uid']}'");

							if ($hit) {
								clearTableName($hit);
								if(!$hit['outcode'])$hit['outcode'] = $hit['my_user'];
								$global[$new['ordernum']] = $hit['outcode'];
							}

						}
						$new2[] = $new;
					}
				}

				$dd = array();
				foreach ($new2 as $n) {

					if (isset($global[$n['ordernum']])) {
						$n['outcode'] = $global[$n['ordernum']];
						$n['source'] = $source[$n['ordernum']];
					}
					else {
						if (isset($global_jumper[$n['jumper_uid']][$n['p_seller']])) {
							$n['outcode'] = $global_jumper[$n['jumper_uid']][$n['p_seller']];
							$n['source'] = $source_jumper[$n['jumper_uid']][$n['p_seller']];
						}
					}

					if ($n['outcode'] == 'test')
						continue;

					if (($n['source'] == 1 || !$n['outcode']) && $n['p_fanli']>1 && rand(0,9)<2 && !$this->OrderFanli->find(array('did'=>$n['did']))){
						$dd[] = $n;
						continue;
					}

					if (!$this->OrderFanli->find(array('did' => $n['did'], 'status' => $n['status']))) {

						if ($id = $this->OrderFanli->field('id', array('did' => $n['did']))) {
							$n['id'] = $id;
						}

						$this->OrderFanli->create();
						$this->OrderFanli->save($n);
						$fanli += number_format($n['p_fanli'], 2);
						$i++;
					}
				}

				foreach($dd as $d){
					$df += number_format($d['p_fanli'], 2);
					$do .= ','.$d['ordernum'];
				}


				$fanli = floatval($fanli);
				$message .= "p_fanli: {$df}<br />p_order: {$do} <br /><br /><br />orders: {$i} fanli: {$fanli}  rate: " . C('config', 'RATE') * 100 . "%";

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

		$this->doRestore();

		$total_date = date('Y-m-d', time() - 8 * 24 * 3600);
		$total_date_month = date('Y-m-d', time() - 31 * 24 * 3600);
		$total_51fanli_earn = $this->UserFanli->findSum('fl_cash');
		$total_51fanli_earn_hist = $this->UserFanli->findSum('fl_cash_history');
		$total_51fanli_fb = $this->UserFanli->findSum('fl_fb') / 100;
		$total_fanli = $this->OrderFanli->findSum('p_fanli', "payed=0 AND status=1 AND donedate<='{$total_date}'");
		$total_yongjin = $this->OrderFanli->findSum('p_yongjin', "payed=0 AND status=1 AND donedate<='{$total_date}'");
		$total_fanli_orders = $this->OrderFanli->findCount("payed=0 AND status=1 AND donedate<='{$total_date}'");

		$total_fanli_month = $this->OrderFanli->findSum('p_fanli', "payed=0 AND status=1 AND donedate<='{$total_date_month}'");
		$total_yongjin_month = $this->OrderFanli->findSum('p_yongjin', "payed=0 AND status=1 AND donedate<='{$total_date_month}'");
		$total_fanli_orders_month = $this->OrderFanli->findCount("payed=0 AND status=1 AND donedate<='{$total_date_month}'");

		//总利润 = 返利网推荐现金 + 米折网总历史现金 - 返利网FB(还没提出) - 已结算
		//$history_51fanli_fanli = $this->UserFanli->findSum('fl_cash');
		//$history_51fanli_fb = $this->UserFanli->findSum('fl_fb')/100;
		$history_mizhe_cash = $this->UserMizhe->findSum('cash_history');
		$total_mizhe_payed = $this->OrderFanli->findSum('p_fanli', array('status' => 1, 'type' => 2));
		$total_earn = $total_51fanli_earn + $history_mizhe_cash + $total_51fanli_earn_hist - $total_mizhe_payed;

		//等待支取
		$waiting_51fanli_cash = $this->UserFanli->findSum('fl_cash');
		$waiting_51fanli_fb = $this->UserFanli->findSum('fl_fb') / 100;
		$waiting_mizhe_cash = $this->UserMizhe->findSum('cash');
		$total_waiting = number_format($waiting_51fanli_cash + $waiting_51fanli_fb + $waiting_mizhe_cash, 2);

		$this->set('message', $message);
		$this->set('total_fanli', $total_fanli);
		$this->set('total_yongjin', $total_yongjin);
		$this->set('total_date', $total_date);
		$this->set('total_fanli_orders', $total_fanli_orders);
		$this->set('total_fanli_month', $total_fanli_month);
		$this->set('total_yongjin_month', $total_yongjin_month);
		$this->set('total_date_month', $total_date_month);
		$this->set('total_fanli_orders_month', $total_fanli_orders_month);
		$this->set('total_waiting', $total_waiting);
		$this->set('total_earn', $total_earn);
	}

	function doPay() {

		$last = date('Y-m-d', time() - 8 * 24 * 3600);
		$orders = $this->OrderFanli->findAll("payed=0 AND status=1 AND donedate<='{$last}'");
		clearTableName($orders);
		$i = 0;
		$fanli = 0;
		foreach ($orders as $order) {
			$this->OrderFanli->save(array('id' => $order['id'], 'payed' => 1, 'payed_date' => $last));
			$fanli += $order['p_fanli'];
			$i++;
		}

		echo "<script>alert('payed {$i} orders : {$fanli}');window.location.href='/Default/index/pub'</script>";
		die();
	}

	//15天以前被暂停的推手，如果账户无资产，则恢复身份并清空pause_date
	//如果有资产，则查询order判断购物金额是否大于30，如果小于30则重新恢复身份
	function doRestore() {

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

}

?>