<?php

class OrderController extends AppController {

	var $name = 'Order';
	var $uses = array('UserFanli', 'OrderFanli', 'UserMizhe', 'UserBaobeisha', 'StatJump');
	const TYPE_FANLI = 1;
	const TYPE_MIZHE = 2;
	const TYPE_GEIHUI = 3;
	const TYPE_BAOBEISHA = 4;
	const TYPE_JSFANLI = 5;
	const TYPE_FANXIAN = 6;
	const TYPE_FLK123 = 7;


	//提交返利网订单数据
	function postFanliOrder(){

		//提交了订单入库
		if (isset($_FILES['file'])) {
			$file = file_get_contents($_FILES["file"]["tmp_name"]);

			//去除UTF BOM头
			if (substr($file, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf)) {
				$file = substr($file, 3);
			}

			$datas = explode("\r\n", trim($file));
			$global = array();
			$global_jumper = array();


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
								$new['p_yongjin'] = $d[7]*0.5;
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
				$fanli = 0;
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


				$fanli = floatval($fanli);
				$message .= "orders: {$i} fanli: {$fanli}  rate: " . C('config', 'RATE') * 100 . "%";

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

			echo $message;
			die();
		}
	}


	//提交米折网订单列表页面
	function postMizheOrder(){

		if (isset($_FILES['file'])) {
			$file = file_get_contents($_FILES["file"]["tmp_name"]);
			//$userid = $_POST['userid'];
			if(!$file){
				die('please input userid & file');
			}

			require_once MYLIBS . 'html_dom.class.php';
			$global = array();
			$global_jumper = array();

			$i = $file;

			if ($i) {
				$html = new simple_html_dom($i);
				$user_dom = $html->find('span[class=mline] a[href=http://i.mizhe.com]', 0);
				$email = $user_dom->text();
				$userid = $this->UserMizhe->field('userid', array('email'=>$email));
				if(!$userid)die($email . ' can not be match userid');

				//如果匹配收入，则为个人中心首页，更新资产即退出
				$in_hist = $html->find('span[class=price c-999] em', 0);
				if($in_hist){
					$in_hist =  $in_hist->text();
					$in_left = $html->find('span[class=green-price] em', 0);
					if($in_left)$in_left = $in_left->text();
					$this->UserMizhe->save(array('userid'=>$userid, 'cash'=>$in_left, 'cash_history'=>$in_hist));
					echo "userid: {$userid} {$email} &nbsp;&nbsp;cash: {$in_left}&nbsp;&nbsp; history: {$in_hist}";
					die();
				}

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
							$order['p_yongjin'] = $p_yongjin * 100 / C('config', 'RATE_MIZHE'); //米折网折扣
							$order['p_fanli'] = $order['p_yongjin'] * C('config', 'RATE');

							$order['p_rate'] = C('config', 'RATE');
							$order['jumper_uid'] = $userid;
							//去除内部卖家
							if (in_array($order['p_seller'], C('config', 'HOLD_SELLER'))) {
								continue;
							}

							$order['type'] = self::TYPE_MIZHE;

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

				$return = $this->_saveOrder($new, $global, $global_jumper);

				$fanli = intval($return['fanli']);
				$order = intval($return['order']);
				$message = "{$userid} orders: {$order} fanli: {$fanli} rate: " . C('config', 'RATE') * 100 . "%";
				echo $message;
				br();
			}
		}
		die();
	}


	//提交给惠网订单列表页面
	function postGeihuiOrder(){

		if (isset($_FILES['file'])) {
			$file = file_get_contents($_FILES["file"]["tmp_name"]);
			//$userid = $_POST['userid'];
			if(!$file){
				die('please input userid & file');
			}

			require_once MYLIBS . 'html_dom.class.php';
			$global = array();
			$global_jumper = array();

			$i = $file;

			if ($i) {
				$html = new simple_html_dom($i);
				$doms = $html->find('tr[class=list_mrow]');
				$new = array();
				foreach ($doms as $dom) {
					$cell = $dom->find('td');
					$single = array();
					foreach($cell as $c){

						$single[] = trim(strip_tags($c));
					}

					pr($single);continue;

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
					$order['p_yongjin'] = $p_yongjin * 100 / C('config', 'RATE_MIZHE'); //米折网折扣
					$order['p_fanli'] = $order['p_yongjin'] * C('config', 'RATE');

					$order['p_rate'] = C('config', 'RATE');
					$order['jumper_uid'] = $userid;
					//去除内部卖家
					if (in_array($order['p_seller'], C('config', 'HOLD_SELLER'))) {
						continue;
					}

					$order['type'] = self::TYPE_GEIHUI;

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

				$return = $this->_saveOrder($new, $global, $global_jumper);

				$fanli = intval($return['fanli']);
				$order = intval($return['order']);
				$message = "{$userid} orders: {$order} fanli: {$fanli} rate: " . C('config', 'RATE') * 100 . "%";
				echo $message;
				br();
			}
		}
		die();
	}

	//提交宝贝杀订单列表页面
	function postBaobeishaOrder(){
		if (isset($_FILES['file'])) {
			$file = file_get_contents($_FILES["file"]["tmp_name"]);
			if(!$file){
				die('please input file');
			}

			require_once MYLIBS . 'html_dom.class.php';

			$global = array();
			$global_jumper = array();

			$i = $file;

			if ($i) {
				$html = new simple_html_dom($i);
				$name_dom = $html->find('div[class=user-nick]', 0);
				$userid = '';
				if($name_dom){
					$username = trim(strip_tags($name_dom));
					$userid = $this->UserBaobeisha->field('userid', "email like '{$username}@%'");
				}

				if(!$userid){
					echo 'Baobeisha user match error!';
					die();
				}
				$doms = $html->find('tr[class=tr]');
				$new = array();
				foreach ($doms as $dom) {
					$cell = $dom->find('td');
					$single = array();
					foreach($cell as $c){
						$single[] = trim(strip_tags($c));
					}

					$order = array();
					$order['p_id'] = $c->getAttribute('iid');
					$order['did'] = $c->getAttribute('trade_id');
					$order['ordernum'] = $single[0];
					$order['p_title'] = $single[1];
					$order['p_price'] = $single[2];
					$order['p_yongjin'] = intval($single[3])/100 * 100 / C('config', 'RATE_BAOBEISHA');
					$order['p_fanli'] = $order['p_yongjin'] * C('config', 'RATE');
					$order['donedate'] = $single[4];
					$order['donedatetime'] = $single[4];

					//下单日期反推10天
					$order['buydate'] = date('Y-m-d', strtotime($order['donedate']) - 10 * 24 * 3600);
					$order['buydatetime'] = date('Y-m-d H:i:s', strtotime($order['donedatetime']) - 10 * 24 * 3600);

					$order['jumper_uid'] = $userid;

					$order['type'] = self::TYPE_BAOBEISHA;

					//如果能正常访问到页面，但解析错误，报警
					if ($order['p_price'] < 1 || !$order['p_title']) {
						alert('rsync baobeisha order', 'userid : ' . $userid . ' content error');
						continue;
					}

					//关联jump记录
					$date_start = date('Y-m-d', strtotime($order['donedatetime']) - 12 * 24 * 3600);
					$hit = $this->StatJump->find("p_id = {$order['p_id']} AND created>'{$date_start}'");

					if ($hit) {
						clearTableName($hit);
						$global[$order['ordernum']] = $hit['outcode'];
					}

					$new[] = $order;
				}

				$return = $this->_saveOrder($new, $global, $global_jumper);

				$fanli = intval($return['fanli']);
				$order = intval($return['order']);
				$message = "<b>{$username}</b> orders: <b>{$order}</b> fanli: <b>{$fanli}</b> rate: " . C('config', 'RATE') * 100 . "%";
				echo $message;
				br();
			}
		}
		die();
	}

	//提交金沙返利订单列表页面
	function postJsfanliOrder(){
		if (isset($_FILES['file'])) {
			$file = file_get_contents($_FILES["file"]["tmp_name"]);
			if(!$file){
				die('please input file');
			}

			require_once MYLIBS . 'html_dom.class.php';

			$global = array();
			$global_jumper = array();

			$i = $file;

			if ($i) {
				$html = new simple_html_dom($i);
				$name_dom = $html->find('div[class=user-nick]', 0);
				$userid = '';
				if($name_dom){
					$username = trim(strip_tags($name_dom));
					$userid = $this->UserBaobeisha->field('userid', "email like '{$username}@%'");
				}

				if(!$userid){
					echo 'Baobeisha user match error!';
					die();
				}
				$doms = $html->find('tr[class=tr]');
				$new = array();
				foreach ($doms as $dom) {
					$cell = $dom->find('td');
					$single = array();
					foreach($cell as $c){
						$single[] = trim(strip_tags($c));
					}

					$order = array();
					$order['p_id'] = $c->getAttribute('iid');
					$order['did'] = $c->getAttribute('trade_id');
					$order['ordernum'] = $single[0];
					$order['p_title'] = $single[1];
					$order['p_price'] = $single[2];
					$order['p_yongjin'] = intval($single[3])/100 * 100 / C('config', 'RATE_BAOBEISHA');
					$order['p_fanli'] = $order['p_yongjin'] * C('config', 'RATE');
					$order['donedate'] = $single[4];
					$order['donedatetime'] = $single[4];

					//下单日期反推10天
					$order['buydate'] = date('Y-m-d', strtotime($order['donedate']) - 10 * 24 * 3600);
					$order['buydatetime'] = date('Y-m-d H:i:s', strtotime($order['donedatetime']) - 10 * 24 * 3600);

					$order['jumper_uid'] = $userid;

					$order['type'] = self::TYPE_JSFANLI;

					//如果能正常访问到页面，但解析错误，报警
					if ($order['p_price'] < 1 || !$order['p_title']) {
						alert('rsync baobeisha order', 'userid : ' . $userid . ' content error');
						continue;
					}

					//关联jump记录
					$date_start = date('Y-m-d', strtotime($order['donedatetime']) - 12 * 24 * 3600);
					$hit = $this->StatJump->find("p_id = {$order['p_id']} AND created>'{$date_start}'");

					if ($hit) {
						clearTableName($hit);
						$global[$order['ordernum']] = $hit['outcode'];
					}

					$new[] = $order;
				}

				$return = $this->_saveOrder($new, $global, $global_jumper);

				$fanli = intval($return['fanli']);
				$order = intval($return['order']);
				$message = "<b>{$username}</b> orders: <b>{$order}</b> fanli: <b>{$fanli}</b> rate: " . C('config', 'RATE') * 100 . "%";
				echo $message;
				br();
			}
		}
		die();
	}

	//提交返现网订单列表页面
	function postFanxianOrder(){

	}

	//提交返利客123订单列表页面
	function postFlk123Order(){

	}

	function _saveOrder($new, $global, $global_jumper){

		$i = 0;
		$fanli = 0;
		foreach ($new as $n) {

			if (isset($global[$n['ordernum']])) {
				$n['outcode'] = $global[$n['ordernum']];
			}
			else {
				if ($global_jumper && isset($global_jumper[$n['jumper_uid']][$n['p_seller']])) {
					$n['outcode'] = $global_jumper[$n['jumper_uid']][$n['p_seller']];
				}
			}

			if (@$n['outcode'] == 'test')
				continue;

			if ($this->OrderFanli->find(array('ordernum' => $n['ordernum'])))
				continue;

			$this->OrderFanli->create();
			$this->OrderFanli->save($n);
			$fanli += $n['p_fanli'];
			$i++;
		}

		return array('order'=>$i, 'fanli'=>$fanli);
	}

	/**
	 * 进行结算
	 */
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
}

?>