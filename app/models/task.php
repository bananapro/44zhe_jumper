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
}

?>