<?php
class Task extends AppModel {

	var $name = 'Task';
	var $useTable = 'task';


	/**
	 * 返回待转任务
	 * @return type
	 */
	function getTask(){

		$task = $this->find(array('status'=>0), '', 'id asc');
		if($task)return clearTableName($task);
	}

	function getJumper($taskid){

		if(!$taskid)return;
		$my_user = $this->field('my_user', array('id'=>$taskid));
		if($my_user){
			$m = new UserBind();
			return $m->getJumper($my_user);
		}
	}
}

?>