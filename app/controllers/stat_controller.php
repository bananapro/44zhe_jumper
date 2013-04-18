<?php

class StatController extends AppController {

	var $name = 'Stat';
	var $uses = array('UserFanli', 'StatJump', 'UserCandidate', 'Alert', 'OrderFanli', 'UserMizhe');

	function beforeRender() {
		parent::beforeRender();
		$this->set('title', '统计中心');
	}

	function index($type='normal') {
		if ($type == 'ss')
			$type = 'senior';
		else
			$type = 'normal';
		$this->set('type', $type);
	}

	function basic() {
		//当前配置状态
		$config = C('config');
		$this->set('c', $config);

		//昨日、今日关键数据
		$s = array();
		$yesterday = date('Y-m-d', time() - 24 * 3600);
		$today = date('Y-m-d');
		$s['y_reg_num'] = $this->UserFanli->findCount("created>'" . $yesterday . "' AND created<'" . $today . "'");
		$s['t_reg_num'] = $this->UserFanli->findCount("created>'" . $today . "'");

		$s['y_jump_num'] = $this->StatJump->findCount("ts>'" . $yesterday . "' AND ts<'" . $today . "' AND outcode<>'test'");
		$s['t_jump_num'] = $this->StatJump->findCount("ts>'" . $today . "' AND outcode<>'test'");

		$s['y_mizhe_jump_num'] = $this->StatJump->findCount("ts>'" . $yesterday . "' AND ts<'" . $today . "' AND outcode<>'test' AND jumper_type='mizhe'");
		$s['t_mizhe_jump_num'] = $this->StatJump->findCount("ts>'" . $today . "' AND outcode<>'test'  AND jumper_type='mizhe'");

		$s['y_price_num'] = $this->StatJump->findSum('p_price', "ts>'" . $yesterday . "' AND ts<'" . $today . "'");
		$s['t_price_num'] = $this->StatJump->findSum('p_price', "ts>'" . $today . "'");

		$s['y_fanli_num'] = $this->StatJump->findSum('p_fanli', "ts>'" . $yesterday . "' AND ts<'" . $today . "'");
		$s['t_fanli_num'] = $this->StatJump->findSum('p_fanli', "ts>'" . $today . "'");

		$s['y_r_num'] = $this->UserFanli->findCount("ts>'" . $yesterday . "' AND ts<'" . $today . "' AND status=2 AND role=3");
		$s['t_r_num'] = $this->UserFanli->findCount("ts>'" . $today . "' AND status=2 AND role=3");

		$s['total_cash'] = $this->UserFanli->findSum('fl_cash');
		$s['total_fb'] = $this->UserFanli->findSum('fl_fb');

		$s['total_mizhe_cash'] = $this->UserMizhe->findSum('cash');
		$s['total_mizhe_history_cash'] = $this->UserMizhe->findSum('cash_history');

		$this->set('s', $s);

		//跳转中介统计
		$j1 = $this->StatJump->query("SELECT count(*) as nu, jumper_uid, username, a.area, sum(p_price) price, sum(p_fanli) fanli FROM stat_jump a LEFT JOIN user_fanli ON(jumper_uid = userid) WHERE a.ts>'" . $yesterday . "' AND a.ts<'" . $today . "' AND outcode<>'test' AND jumper_type='51fanli' GROUP BY jumper_uid ORDER BY nu DESC");
		$j2 = $this->StatJump->query("SELECT count(*) as nu, jumper_uid, username, a.area, sum(p_price) price, sum(p_fanli) fanli FROM stat_jump a LEFT JOIN user_fanli ON(jumper_uid = userid) WHERE a.ts>'" . $today . "' AND outcode<>'test' AND jumper_type='51fanli' GROUP BY jumper_uid ORDER BY nu DESC");
		clearTableName($j1);
		clearTableName($j2);
		$this->set('js1', $j1);
		$this->set('js2', $j2);


		//最新跳转记录
		$last_jumps = $this->StatJump->findAll('', '', 'id DESC', 15);
		clearTableName($last_jumps);
		$this->set('last_jumps', $last_jumps);

		//最新报警
		$last_alerts = $this->Alert->findAll('', '', 'id DESC', 5);
		clearTableName($last_alerts);
		$this->set('last_alerts', $last_alerts);

		//特殊账号跳转额
		$sp = @file_get_contents('/tmp/overlimit_day/SP_FANLI_MAX/' . date('Ym'));
		$this->set('sp', $sp);

		$mizhe_fanli = @file_get_contents('/tmp/overlimit_day/JUMP_MIZHE_FANLI_MAX/' . date('Ym'));
		$this->set('mizhe_fanli', floatval($mizhe_fanli));
	}

	function dataEarn() {

		$date_num = 14;
		$y_num = 100;
		$senior = $_GET['type'] == 'senior' ? true : false;

		$job_title = $_GET['job'];
		if ($_GET['y_num'])
			$y_num = $_GET['y_num'];
		if ($_GET['date_num'])
			$date_num = $_GET['date_num'];

		$last_date = date('Y-m-d', strtotime('-' . $date_num . ' day'));
		$colors = array('0x9933CC', '0x50CC33', '0x736AFF', '0xF50505', '0xD54C78', '0x3334AD',
			'0x339966', '0xF50505', '0x8BB1A1', 'BAA8BD', '0xC31812', '0x424581',
			'0x736AFF', '0x6AFF73', '0xADB5C7', '0xC11B01', '0x9933CC',
			'0xd070ac', '0x799191', '0x7D8E99');

		$datas = $this->OrderFanli->query("SELECT sum(p_fanli) as fanli,status,donedate FROM order_fanli WHERE donedate>'$last_date' GROUP BY donedate,status");
		clearTableName($datas);
		$new_datas = array();

		$max_node = array();

		foreach ($datas as $data) {
			$node_name = $data['status'];
			switch ($data['status']) {
				case '1':$node_name = '已确认结算金额(元)';
					break;
				case '3':$node_name = '订单确认中(10天)';
					break;
				case '4':$node_name = '订单确认中(20天)';
					break;
			}
			@$new_datas[$node_name][$data['donedate']] = $data['fanli'];
		}

		if ($senior) {
			$datas = $this->OrderFanli->query("SELECT sum(p_fanli) as fanli,donedate FROM order_fanli WHERE type=2 AND donedate>'$last_date' GROUP BY donedate");
			clearTableName($datas);
			foreach ($datas as $data) {
				@$new_datas['Mi结算'][$data['donedate']] = $data['fanli'];
			}
		}

		require_once MYLIBS . 'ofc-library/open-flash-chart.php';
		$g = new graph();
		$g->title('返利网每日返现统计 (每周更新-按订单完成日期)', '{font-size: 16px; color: #736AFF}');

		$max = array();
		foreach ($new_datas as $node => $data) {
			//if(!array_search($node, array_keys($max_node)))continue;
			$every_date = array();
			for ($i = $date_num; $i > -1; $i--) {

				$every_date[] = intval(@$data[date('Y-m-d', strtotime("-$i day"))]);
			}
			$max[] = max($every_date);
			//pr($every_date);
			$g->set_data($every_date);
			$g->line(2, array_shift($colors), $node, 12);
		}

		$date_arr = array();
		for ($i = $date_num; $i > -1; $i--) {
			$date_arr[] = date('d', strtotime("-$i day"));
		}

		$g->set_x_labels($date_arr);
		$g->set_x_label_style(10, '0x000000', 0, 2);

		$g->set_y_max(ceil(max($max) / $y_num) * $y_num);
		$g->y_label_steps(10);
		$g->set_y_legend($job_title . ' chart', 12, '#736AFF');
		echo $g->render();
		die();
	}

	function dataBuy() {

		$date_num = 14;
		$y_num = 100;

		$job_title = $_GET['job'];
		if ($_GET['y_num'])
			$y_num = $_GET['y_num'];
		if ($_GET['date_num'])
			$date_num = $_GET['date_num'];

		$senior = $_GET['type'] == 'senior' ? true : false;

		$last_date = date('Y-m-d', strtotime('-' . $date_num . ' day'));
		$colors = array('0x9933CC', '0x50CC33', '0x736AFF', '0xF50505', '0xD54C78', '0x3334AD',
			'0x339966', '0xF50505', '0x8BB1A1', 'BAA8BD', '0xC31812', '0x424581',
			'0x736AFF', '0x6AFF73', '0xADB5C7', '0xC11B01', '0x9933CC',
			'0xd070ac', '0x799191', '0x7D8E99');

		$datas = $this->OrderFanli->query("SELECT sum(p_fanli) as fanli,status,buydate FROM order_fanli WHERE buydate>'$last_date' GROUP BY buydate,status");
		clearTableName($datas);
		$new_datas = array();

		$max_node = array();

		foreach ($datas as $data) {
			$node_name = $data['status'];
			switch ($data['status']) {
				case '1':$node_name = '已确认结算金额(元)';
					break;
				case '3':$node_name = '订单无效金额(10天)';
					break;
				case '4':$node_name = '订单确认中(20天)';
					break;
			}
			@$new_datas[$node_name][$data['buydate']] = $data['fanli'];
		}

		if ($senior) {
			$datas = $this->OrderFanli->query("SELECT sum(p_fanli) as fanli,buydate FROM order_fanli WHERE type=2 AND buydate>'$last_date' GROUP BY buydate");
			clearTableName($datas);
			foreach ($datas as $data) {
				@$new_datas['Mi结算'][$data['buydate']] = $data['fanli'];
			}
		}

		require_once MYLIBS . 'ofc-library/open-flash-chart.php';
		$g = new graph();
		$g->title('返利网每日返现统计 (每周更新-按订单下单日期)', '{font-size: 16px; color: #736AFF}');

		$max = array();
		foreach ($new_datas as $node => $data) {
			//if(!array_search($node, array_keys($max_node)))continue;
			$every_date = array();
			for ($i = $date_num; $i > -1; $i--) {

				$every_date[] = intval(@$data[date('Y-m-d', strtotime("-$i day"))]);
			}
			$max[] = max($every_date);
			//pr($every_date);
			$g->set_data($every_date);
			$g->line(2, array_shift($colors), $node, 12);
		}

		$date_arr = array();
		for ($i = $date_num; $i > -1; $i--) {
			$date_arr[] = date('d', strtotime("-$i day"));
		}

		$g->set_x_labels($date_arr);
		$g->set_x_label_style(10, '0x000000', 0, 2);

		$g->set_y_max(ceil(max($max) / $y_num) * $y_num);
		$g->y_label_steps(10);
		$g->set_y_legend($job_title . ' chart', 12, '#736AFF');
		echo $g->render();
		die();
	}

	function dataJump() {

		$date_num = 14;
		$y_num = 100;

		$job_title = $_GET['job'];
		if ($_GET['y_num'])
			$y_num = $_GET['y_num'];
		if ($_GET['date_num'])
			$date_num = $_GET['date_num'];

		$senior = $_GET['type'] == 'senior' ? true : false;

		$last_date = date('Y-m-d', strtotime('-' . $date_num . ' day'));
		$colors = array('0x9933CC', '0x50CC33', '0x736AFF', '0x736AFF', '0xD54C78', '0x3334AD',
			'0x339966', 'F50505', '0x8BB1A1', 'BAA8BD', '0xC31812', '0x424581',
			'0x736AFF', '0x6AFF73', '0xADB5C7', '0xC11B01', '0x9933CC',
			'0xd070ac', '0x799191', '0x7D8E99');

		$datas = $this->StatJump->query("SELECT sum(p_price) as price, sum(p_fanli) as fanli,count(*) as nu,DATE(created) as created FROM stat_jump WHERE DATE(created)>'$last_date' GROUP BY DATE(created)");
		clearTableName($datas);
		$new_datas = array();
		$max_node = array();

		foreach ($datas as $data) {
			//@$new_datas['总金额'][$data['created']] = intval($data['price']);
			@$new_datas['总佣金(元)'][$data['created']] = intval($data['fanli']);
			@$new_datas['跳转次数'][$data['created']] = $data['nu'];
		}

		if ($senior) {
			$datas = $this->StatJump->query("SELECT sum(p_price) as price, sum(p_fanli) as fanli,count(*) as nu,DATE(created) as created FROM stat_jump WHERE jumper_type='mizhe' AND DATE(created)>'$last_date' GROUP BY DATE(created)");
			clearTableName($datas);
			foreach ($datas as $data) {
				@$new_datas['Mi佣金(元)'][$data['created']] = intval($data['fanli']);
			}
		}

		require_once MYLIBS . 'ofc-library/open-flash-chart.php';
		$g = new graph();
		$g->title('每日跳转统计 (实时更新)', '{font-size: 16px; color: #736AFF}');

		$max = array();
		foreach ($new_datas as $node => $data) {
			//if(!array_search($node, array_keys($max_node)))continue;
			$every_date = array();
			for ($i = $date_num; $i > -1; $i--) {

				$every_date[] = intval(@$data[date('Y-m-d', strtotime("-$i day"))]);
			}
			$max[] = max($every_date);
			//pr($every_date);
			$g->set_data($every_date);
			$g->line(2, array_shift($colors), $node, 12);
		}

		$date_arr = array();
		for ($i = $date_num; $i > -1; $i--) {
			$date_arr[] = date('d', strtotime("-$i day"));
		}

		$g->set_x_labels($date_arr);
		$g->set_x_label_style(10, '0x000000', 0, 2);

		$g->set_y_max(ceil(max($max) / $y_num) * $y_num);
		$g->y_label_steps(10);
		$g->set_y_legend($job_title . ' chart', 12, '#736AFF');
		echo $g->render();
		die();
	}

	function jump($date = null) {
		if (!$date)
			$date = date('Y-m-d');

		$tomo = date('Y-m-d', strtotime($date) + 24 * 3600);
		//最新跳转记录
		$last_jumps = $this->StatJump->findAll("ts>'{$date}' AND ts<'{$tomo}' AND outcode<>'test'", '', 'id DESC');
		clearTableName($last_jumps);
		$this->set('last_jumps', $last_jumps);
		$this->set('date', $date);
	}

}

?>