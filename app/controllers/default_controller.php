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


		$this->UserFanli->doRestore();

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
}

?>