<?php

class DefaultController extends AppController {

	var $name = 'Default';
	var $uses = array('OrderFanli', 'StatJump', 'OrderTmp', 'UserFanli', 'UserMizhe', 'UserBind', 'UserFanxian');
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


		$this->UserFanli->doRestore();

		$total_date = date('Y-m-d', time() - 8 * 24 * 3600); //结算至
		$total_date_month = date('Y-m-d', time() - 31 * 24 * 3600);
		$total_51fanli_earn = $this->UserFanli->findSum('fl_cash', array('role'=>2)) + $this->UserFanli->findSum('fl_cash_history', array('role'=>2));
		$total_51fanli_earn_b2c = $this->UserFanli->findSum('fl_cash', array('role'=>4)) + $this->UserFanli->findSum('fl_cash_history', array('role'=>4));
		$total_fanli = $this->OrderFanli->findSum('p_fanli', "payed=0 AND status=1 AND donedate<='{$total_date}'");
		$total_yongjin = $this->OrderFanli->findSum('p_yongjin', "payed=0 AND status=1 AND donedate<='{$total_date}'");
		$total_fanli_orders = $this->OrderFanli->findCount("payed=0 AND status=1 AND donedate<='{$total_date}'");

		$total_fanli_month = $this->OrderFanli->findSum('p_fanli', "payed=0 AND status=1 AND donedate<='{$total_date_month}'");
		$total_yongjin_month = $this->OrderFanli->findSum('p_yongjin', "payed=0 AND status=1 AND donedate<='{$total_date_month}'");
		$total_fanli_orders_month = $this->OrderFanli->findCount("payed=0 AND status=1 AND donedate<='{$total_date_month}'");

		//总利润 = 返利网推荐现金 + 米折网总历史现金 - 米折网所有订单阿雄返利 - 米折已冻结*25
		//$history_51fanli_fanli = $this->UserFanli->findSum('fl_cash');
		//$history_51fanli_fb = $this->UserFanli->findSum('fl_fb')/100;
		$history_mizhe_cash = $this->UserMizhe->findSum('cash_history');
		$total_mizhe_p_fanli = $this->OrderFanli->findSum('p_fanli', array('status' => 1, 'type' => 2));
		$total_mizhe_cash_error = $this->UserMizhe->findSum('cash_error');
		$total_earn = $total_51fanli_earn + $total_51fanli_earn_b2c*0.5 + $history_mizhe_cash  - $total_mizhe_p_fanli - $total_mizhe_cash_error * 0.25;

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

	function info($info=''){

		$ip = getip();
		if($info=='ip'){
                        echo $ip;die();
                }
		$area = getAreaByIp();
		$agent = $_SERVER['HTTP_USER_AGENT'];

		$last_info = cache('proxy_info', null, 86400*365);
		$new_info = array('ip'=>$ip, 'area'=>$area, 'agent'=>$agent);
		if($last_info){
			array_unshift($last_info, $new_info);
		}else{
			$last_info[] = $new_info;
		}

		if(count($last_info) > 8){
			array_pop($last_info);
		}

		cache('proxy_info', $last_info);
		$this->set('info', $last_info);
	}

	function test($get=false){

		$d = date('Ymd');
		$file = '/tmp/overlimit_day/REG_COMMON_PRE_DAY_LIMIT/' . $d;
		if(!$get)
			unlink($file);
		else
			echo file_get_contents($file);
		die();
	}


	function matchFanxianOrder(){

		$orders = $this->OrderTmp->findAll();
		clearTableName($orders);
		$hit_orders = array();
		foreach ($orders as $order) {

			if($hit = $this->StatJump->find("p_title like '".$order['title']."%' AND created > '2013-10-31' AND created < '2013-11-17' AND jumper_type='fanxian'")){
				clearTableName($hit);

				$email = $this->UserFanxian->field('email', array('userid'=>$hit['jumper_uid']));
				$hit_orders[$hit['jumper_uid']][] = array('order_num'=>$order['order_num'],'buy_time'=>$order['buy_time'],'all_time'=>$order['all_time'], 'email'=>$email, 'p_price'=>$hit['p_price'], 'p_fanli'=>$hit['p_fanli']);;
			}
		}

		foreach($hit_orders as $userid => $orders){

			foreach($orders as $order){
				echo $order['order_num'];
				if($order['all_time'])
					echo " | " . $order['all_time'];
				else
					echo " | " . $order['buy_time'];
				echo " | " . $order['email'];
				echo " | " . $order['p_price'];
				echo " - " . $order['p_fanli'];
				echo "<br />\n";
			}
		}
		die();
	}
}

?>
