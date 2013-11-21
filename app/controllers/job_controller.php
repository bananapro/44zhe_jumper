<?php

class JobController extends AppController {

	var $name = 'Job';
	var $uses = array('UserFanli', 'StatJump', 'OrderFanli', 'UserMizhe', 'SmsCode', 'Task');

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

	/**
	 * 提交/获取指定手机号验证码
	 * raw:true 接口请求模式
	 */
	function smsCode($raw = false){

		$flush = true;
		$error = false;
		//提交数据设置任务
		if(@$_POST['mobile']){
			//清除mobile code值
			$sid = $_COOKIE['PHPSESSID'];
			$uid = $_COOKIE['prouserid'];
			if(!$sid || !$uid){
				$error = true;
			}else{
				$this->SmsCode->save(array('id'=>1, 'mobile'=>trim($_POST['mobile']), 'sid'=>$sid, 'uid'=>$uid, 'code'=>''));
				if($raw){
					echo 'succ';die();
				}
			}
		}

		$hit_code = $this->SmsCode->find("id=1 AND code <> ''");

		if($raw){//接口模式直接返回，接口使用非阻塞请求
			clearTableName($hit_code);
			echo @$hit_code['code'];
			die();
		}

		if($hit_code && !$error){
			//不再刷新页面
			clearTableName($hit_code);
			$flush = false;
			$this->set('code', $hit_code);
		}else if($error){
			$flush = false;
			$this->set('code', false);
		}

		$this->set('error', $error);
		$this->set('flush', $flush);
	}

	function nojobs() {

		echo 'jobs all done!';
		die();
	}


}

?>