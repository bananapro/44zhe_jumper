
<?php

class TargetController extends AppController {

	var $name = 'Target';
	var $uses = array('Target');
	var $layout = 'ajax';

	/**
	 * worker获取一个待处理任务
	 * @return [json] [任务详情]
	 */
	function getTask(){

		$t_info = $this->Target->find("status=0", '', 'id asc');
		clearTableName($t_info);
		if($t_info){
			$this->Target->save(array('id'=>$t_info['id'], 'status'=>1));
			$this->_success($t_info, true);
		}else{
			$this->_success(0, true);
		}
	}

	/**
	 * worker结束待处理任务
	 * @return [json] [处理结果]
	 */
	function finishTask($taskid, $status=1){
		if(!$taskid)$this->_error('任务ID不能为空!');

		if(!@$_GET['msg'])$_GET['msg'] = '';
		$this->Target->save(array('id'=>$taskid, 'status'=>$status, 'error_msg'=>$_GET['msg']));

		$this->_success('ok', true);
	}
}

?>