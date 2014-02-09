<?php

class OrderController extends AppController {

	var $name = 'Order';
	var $uses = array('UserFanli', 'OrderFanli', 'UserMizhe', 'UserBaobeisha', 'UserFlk123', 'UserTaofen8', 'UserJsfanli', 'UserFanxian', 'UserGeihui', 'UserJuanpi', 'StatJump');
	const TYPE_FANLI = 1;
	const TYPE_MIZHE = 2;
	const TYPE_GEIHUI = 3;
	const TYPE_BAOBEISHA = 4;
	const TYPE_JSFANLI = 5;
	const TYPE_FANXIAN = 6;
	const TYPE_FLK123 = 7;
	const TYPE_TAOFEN8 = 8;
	const TYPE_JUANPI = 9;

	//统一提交此处，再转交
	function post(){

		if (!empty($_FILES)) {
			//$tempFile = $_FILES['file']['tmp_name'];
			echo $_FILES['file']['name'] . ' &nbsp; : &nbsp; ';

			if(strpos($_FILES['file']['name'], '宝贝杀')!==false ||
				strpos($_FILES['file']['name'], 'baobeisha')!==false
			){
				$this->postBaobeishaOrder();
			}

			if(strpos($_FILES['file']['name'], 'fanxian')!==false ||
				strpos($_FILES['file']['name'], '返现')!==false
			){
				$this->postFanxianOrder();
			}

			if(strpos($_FILES['file']['name'], '金沙')!==false || strpos($_FILES['file']['name'], 'jsfanli')!==false
			){
				$this->postJsfanliOrder();
			}

			if(strpos($_FILES['file']['name'], '卷皮')!==false || strpos($_FILES['file']['name'], 'juanpi')!==false
			){
				$this->postJuanpiOrder();
			}

			echo 'order do not know';
			die();
		}
	}

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
			$i_hit = 0;

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
								$i_hit++;
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

							if(!intval($d[7])){//没有佣金字段，直接填充返利字段
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
								$i_hit++;
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
			$message .= "orders: {$i} fanli: {$fanli}  rate: " . C('config', 'RATE') * 100 . "% hit: ".$i_hit;

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
					}
				}
			}
			else {
				$message = 'file format error!';
			}

			echo $message;
			die();
		}
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
					$userid = $this->UserBaobeisha->field('userid', "email like '{$username}%'");
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
					$order['p_rate'] = C('config', 'RATE');
					$order['donedate'] = $single[4];
					$order['donedatetime'] = $single[4];

					//下单日期反推10天
					$order['buydate'] = date('Y-m-d', strtotime($order['donedate']) - 10 * 24 * 3600);
					$order['buydatetime'] = date('Y-m-d H:i:s', strtotime($order['donedatetime']) - 10 * 24 * 3600);

					$order['jumper_uid'] = $userid;

					$order['type'] = self::TYPE_BAOBEISHA;

					//如果能正常访问到页面，但解析错误，报警
					if ($order['p_price'] < 1 || !$order['p_title'] || !$order['p_id']) {
						alert('rsync baobeisha order', 'userid : ' . $userid . ' content error');
						continue;
					}

					//关联jump记录
					$date_start = date('Y-m-d', strtotime($order['donedatetime']) - 12 * 24 * 3600);
					$hit = $this->StatJump->find("p_id = {$order['p_id']} AND jumper_type = 'baobeisha' AND created>'{$date_start}'");

					if ($hit) {
						clearTableName($hit);
						$global[$order['ordernum']] = $hit['outcode'];
					}
					$new[] = $order;
				}

				$return = $this->_saveOrder($new, $global, $global_jumper);

				$fanli = intval($return['fanli']);
				$order = intval($return['order']);
				$message = "<b>{$username}</b> orders: <b>{$order}</b> fanli: <b>{$fanli}</b> rate: " . C('config', 'RATE') * 100 . "% hit: " . $return['hit'];
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
					$userid = $this->UserJsfanli->field('userid', "email like '{$username}%'");
				}

				if(!$userid){
					echo 'Jsfanli user match error!';
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
					$order['p_yongjin'] = intval($single[3])/100 * 100 / C('config', 'RATE_JSFANLI');
					$order['p_fanli'] = $order['p_yongjin'] * C('config', 'RATE');
					$order['p_rate'] = C('config', 'RATE');
					$order['donedate'] = $single[4];
					$order['donedatetime'] = $single[4];

					//下单日期反推10天
					$order['buydate'] = date('Y-m-d', strtotime($order['donedate']) - 10 * 24 * 3600);
					$order['buydatetime'] = date('Y-m-d H:i:s', strtotime($order['donedatetime']) - 10 * 24 * 3600);

					$order['jumper_uid'] = $userid;

					$order['type'] = self::TYPE_JSFANLI;

					//如果能正常访问到页面，但解析错误，报警
					if ($order['p_price'] < 1 || !$order['p_title'] || !$order['p_id']) {
						alert('rsync jsfanli order', 'userid : ' . $userid . ' content error');
						continue;
					}

					//关联jump记录
					$date_start = date('Y-m-d', strtotime($order['donedatetime']) - 12 * 24 * 3600);
					$hit = $this->StatJump->find("p_id = {$order['p_id']} AND jumper_type = 'jsfanli' AND created>'{$date_start}'");

					if ($hit) {
						clearTableName($hit);
						$global[$order['ordernum']] = $hit['outcode'];
					}

					$new[] = $order;
				}

				$return = $this->_saveOrder($new, $global, $global_jumper);

				$fanli = intval($return['fanli']);
				$order = intval($return['order']);
				$message = "<b>{$username}</b> orders: <b>{$order}</b> fanli: <b>{$fanli}</b> rate: " . C('config', 'RATE') * 100 . "% hit: " . $return['hit'];
				echo $message;
				br();
			}
		}
		die();
	}

	//提交返现网订单列表页面
	function postFanxianOrder(){
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
				$name_dom = $html->find('li[class=username] a', 0);
				$userid = '';
				if($name_dom){
					$username = trim(strip_tags($name_dom));
					$userid = $this->UserFanxian->field('userid', "email like '{$username}%'");
				}

				if(!$userid){
					die('Fanxian user match error!');
				}

				$doms = $html->find('div[class=qbdd] tr[class=result]');
				$new = array();
				foreach ($doms as $dom) {
					$cell = $dom->find('td');
					$single = array();
					foreach($cell as $c){
						$single[] = trim(strip_tags($c));
					}


					$p_id_a_href = $cell[0]->children(0)->getAttribute('href');
					$order_id_a_href = $cell[5]->find('a',0)->getAttribute('href');
					preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9:]+)/', $single[2], $m);

					$order = array();

					$order['p_title'] = $single[0];
					$order['p_id'] = array_pop(explode("=", $p_id_a_href));

					$order['p_price'] = floatval($single[1]);
					$order['p_yongjin'] = intval($single[3])/100 * 100 / C('config', 'RATE_FANXIAN');
					$order['p_fanli'] = $order['p_yongjin'] * C('config', 'RATE');
					$order['p_rate'] = C('config', 'RATE');
					$order['donedate'] = trim($m[1]);
					$order['donedatetime'] = trim($m[1]);

					$y = md5($order['p_title']);
					$order['did'] = '20' . strtotime($order['donedatetime']) . hexdec($y[1] . $y[2]);

					if(strpos($order_id_a_href, '=')){
						$order['ordernum'] = array_pop(explode("=", $order_id_a_href));
					}else{
						$order['ordernum'] = $order['did'];
					}

					//下单日期反推10天
					$order['buydate'] = date('Y-m-d', strtotime($order['donedate']) - 10 * 24 * 3600);
					$order['buydatetime'] = date('Y-m-d H:i:s', strtotime($order['donedatetime']) - 10 * 24 * 3600);
					$order['jumper_uid'] = $userid;
					$order['type'] = self::TYPE_FANXIAN;

					//如果能正常访问到页面，但解析错误，报警
					if ($order['p_price'] < 1 || !$order['p_title'] || !$order['p_id'] || !$order['did']) {
						alert('rsync fanxian order', 'userid : ' . $userid . ' content error');
						continue;
					}

					//关联jump记录
					$date_start = date('Y-m-d', strtotime($order['donedatetime']) - 12 * 24 * 3600);
					$hit = $this->StatJump->find("p_id = {$order['p_id']} AND jumper_type = 'fanxian' AND created>'{$date_start}'");

					if ($hit) {
						clearTableName($hit);
						$global[$order['ordernum']] = $hit['outcode'];
					}

					$new[] = $order;
				}

				$return = $this->_saveOrder($new, $global, $global_jumper);

				$fanli = intval($return['fanli']);
				$order = intval($return['order']);
				$message = "<b>{$username}</b> orders: <b>{$order}</b> fanli: <b>{$fanli}</b> rate: " . C('config', 'RATE') * 100 . "% hit: " . $return['hit'];;
				echo $message;
				br();
			}
		}
		die();
	}

	//提交卷皮网订单列表页面
	function postJuanpiOrder(){
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
				$name_dom = $html->find('span[class=green]', 0);
				$userid = '';
				if($name_dom){
					$username = trim(strip_tags($name_dom));
					$userid = $this->UserJuanpi->field('userid', "email like '{$username}%'");
				}
				$userid = 1;
				if(!$userid){
					die('Juanpi user match error!');
				}
				$doms = $html->find('ul[class=secondul]');
				$new = array();
				foreach ($doms as $dom) {
					$cell = $dom->find('li');
					$single = array();
					if(preg_match('/([0-9]{5,})/', $cell[1]->find('span', 0), $m)){
						$single[] = $m[1];
					}else{
						die('Juanpi ordernum match error!');
					}
					$single[] = $cell[1]->find('a', 0)->text();
					if(preg_match('/([0-9]{5,})/', $cell[1]->find('a', 0)->href, $m)){
						$single[] = $m[1];
					}else{
						die('Juanpi pid match error!');
					}

					$single[] = str_replace('￥', '', $cell[2]->find('span', 0));
					$single[] = str_replace('￥', '', $cell[2]->find('span', 1));

					if(preg_match('/([0-9\- \:]{5,})/', $cell[3]->find('span',0), $m)){
						$single[] = trim($m[1]);
					}else{
						die('Juanpi done date match error!');
					}

					$single[] = $cell[4];
					foreach($single as &$c){
						$c = trim(strip_tags($c));
					}
					//if(strpos($single[6], '已锁定')!==false)$single[6] = '已返利';
					if($single[6] != '已返利')continue;
					$order = array();
					$order['p_id'] = $single[2];
					$order['did'] = $single[0];
					$order['ordernum'] = $order['did'];
					$order['p_title'] = $single[1];
					$order['p_price'] = $single[3];
					$order['p_yongjin'] = intval($single[4]) * 100 / C('config', 'RATE_JUANPI');
					$order['p_fanli'] = $order['p_yongjin'] * C('config', 'RATE');
					$order['p_rate'] = C('config', 'RATE');
					$order['donedate'] = $single[5];
					$order['donedatetime'] = $single[5];

					//下单日期反推10天
					$order['buydate'] = date('Y-m-d', strtotime($order['donedate']) - 10 * 24 * 3600);
					$order['buydatetime'] = date('Y-m-d H:i:s', strtotime($order['donedatetime']) - 10 * 24 * 3600);

					$order['jumper_uid'] = $userid;

					$order['type'] = self::TYPE_JUANPI;

					//如果能正常访问到页面，但解析错误，报警
					if ($order['p_price'] < 1 || !$order['p_title'] || !$order['p_id']) {
						alert('rsync jsfanli order', 'userid : ' . $userid . ' content error');
						continue;
					}

					//关联jump记录
					$date_start = date('Y-m-d', strtotime($order['donedatetime']) - 12 * 24 * 3600);
					$hit = $this->StatJump->find("p_id = {$order['p_id']} AND jumper_type = 'juanpi' AND created>'{$date_start}'");

					if ($hit) {
						clearTableName($hit);
						$global[$order['ordernum']] = $hit['outcode'];
					}

					$new[] = $order;
				}

				$return = $this->_saveOrder($new, $global, $global_jumper);

				$fanli = intval($return['fanli']);
				$order = intval($return['order']);
				$message = "<b>{$username}</b> orders: <b>{$order}</b> fanli: <b>{$fanli}</b> rate: " . C('config', 'RATE') * 100 . "% hit: " . $return['hit'];
				echo $message;
				br();
			}
		}
		die();
	}

	function _saveOrder($new, $global, $global_jumper){

		$i = 0;
		$fanli = 0;
		$hit = 0;
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

			if(@$n['outcode'])$hit += 1;

			if ($this->OrderFanli->find(array('ordernum' => $n['ordernum'])))
				continue;

			if ($this->OrderFanli->find(array('did' => $n['did'])))
				continue;

			if ($n['p_price']*0.5 < $n['p_yongjin']){
				echo $n['p_title'] . ' price yongjin error: ' . $n['p_price'] . ':' . $n['p_yongjin'];
				die();
			}


			$this->OrderFanli->create();
			$this->OrderFanli->save($n);
			$fanli += $n['p_fanli'];

			$i++;
		}

		return array('order'=>$i, 'fanli'=>$fanli, 'hit'=>$hit);
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