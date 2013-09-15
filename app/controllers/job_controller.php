<?php

class JobController extends AppController {

	var $name = 'Job';
	var $uses = array('UserFanli', 'StatJump', 'OrderFanli', 'UserMizhe', 'Task');

	function beforeRender() {
		parent::beforeRender();
		$this->set('title', '手动作业平台');
	}

	/**
	 * 获取人工推荐任务
	 */
	function getRecommendJobs() {

		$total = $this->UserFanli->findCount(array('role' => 3, 'status' => 1));

		if ($total - C('config', 'LEFT_RECOMMENDER') <= 0) {//预留被推
			$this->redirect('/job/nojobs');
		}

		$n = $total - C('config', 'LEFT_RECOMMENDER');

		$users = $this->UserFanli->findAll(array('role' => 3, 'status' => 1), '', 'rand()', $n);
		clearTableName($users);
		$area = array();
		foreach ($users as $u) {
			@$area[$u['area']] += 1;
		}
		pr($area);
		die();
	}

	/**
	 * 人工处理推荐任务
	 * @param type $id
	 */
	function doRecommendTask($pid = '') {

		if ($pid < 10000) {
			echo 'Pid param must be num!';
			die();
		}

		if ($this->StatJump->find(array('ip' => getip()))) {
			echo 'Please change your ip!';
			die();
		}

		$total = $this->UserFanli->findCount(array('role' => 3, 'status' => 1));

		if ($total - C('config', 'LEFT_RECOMMENDER') <= 0) {//预留被推
			$this->redirect('/job/nojobs');
		}

		if (!$pid) {
			echo 'pid param can not be empty';
			die();
		}
		$this->set('pid', $pid);
	}

	function nojobs() {

		echo 'jobs all done!';
		die();
	}

	function getMizheJumpJob($jumper_uid){

		if(!$jumper_uid)die('empty userid');
		$user = $this->UserMizhe->find(array('userid'=>$jumper_uid));
		$jobs = $this->StatJump->findAll(array('jumper_type'=>'mizhe', 'followed'=>0, 'shop'=>'taobao', 'jumper_uid'=>$jumper_uid), '', '', 1);
		$finished_jobs = $this->StatJump->findCount(array('jumper_type'=>'mizhe', 'followed'=>1, 'shop'=>'taobao', 'jumper_uid'=>$jumper_uid));
		clearTableName($jobs);
		clearTableName($user);

		$this->set('jobs', $jobs);
		$this->set('user', $user);
		$this->set('finished_jobs', $finished_jobs);
	}

	function doMizheJumpJob($id){

		$job = $this->StatJump->find(array('id'=>$id));
		$this->StatJump->save(array('id'=>$id, 'followed'=>1));

		clearTableName($job);
		$rate = number_format($job['p_fanli']*C('config', 'MIZHE_RATE') / $job['p_price'], 2);
		$this->redirect('http://go.mizhe.com/rebate/taobao/i-'.urlencode($job['p_seller']).'-'.$job['p_id'].'.html?stop=0&r='.$rate.'&p='.base64_encode($job['p_price']));
		die();
	}

	function taskWorker(){

		$t_info = $this->Task->find(array('status'=>0), '', 'id asc');
		clearTableName($t_info);
		if($t_info){
			require_once MYLIBS . 'jumper' . DS . "jtask_{$t_info['jumper_type']}.class.php";
			$obj_name = 'Jtask'.ucfirst($t_info['jumper_type']);
			$task = new $obj_name($t_info['id']);
			$task->workConvertLink();
		}

		$total_jobs = $this->Task->findCount(array('status'=>0));
		$this->set('total_jobs', $total_jobs);
	}

}

?>