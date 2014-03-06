
<?php

class TargetController extends AppController {

	var $name = 'Target';
	var $uses = array('Target');
	var $layout = 'ajax';

	/**
	 * worker获取一个待处理任务
	 * @return [json] [任务详情]
	 */
	function getTask($pay=false){

		if(!$pay){
			$t_info = $this->Target->find(array('status'=>array(0,1,2)), '', 'id asc');
		}else{
			$t_info = $this->Target->find(array('status'=>3), '', 'id asc');
		}

		clearTableName($t_info);
		if($t_info){
			//$this->Target->save(array('id'=>$t_info['id'], 'status'=>1));
			$this->_success($t_info, true);
		}else{
			$this->_success(0, true);
		}
	}

	/**
	 * worker结束待处理任务
	 * @return [json] [处理结果]
	 */
	function finishTask($taskid, $status=1, $user_id=0){

		if(!$taskid)$this->_error('任务ID不能为空!');

		if(!@$_GET['error_msg'])$_GET['error_msg'] = '';
		if($user_id){
			$this->Target->save(array('id'=>$taskid, 'status'=>$status, 'error_msg'=>$_GET['error_msg'], 'user_id'=>$user_id));
		}else{
			$this->Target->save(array('id'=>$taskid, 'status'=>$status, 'error_msg'=>$_GET['error_msg']));
		}


		$this->_success('ok', true);
	}

	function changeSafeLevel(){

		$t_info = $this->Target->findAll(array('status'=>3));
		clearTableName($t_info);
		echo "/usr/local/unixODBC/bin/isql prddsn 'fanliapp' '31a@s12d801opa90d2laWwe#jasdiopIOAl!sjd'";
		echo "<br />";

		foreach($t_info as $t){
			echo "update [51fanli].[dbo].[UserSafeLevel] set safelevel=1 where userid={$t['user_id']}";
			echo "<br />";
		}
		die();
	}

	function getDuixianList(){
		$t_info = $this->Target->findAll(array('status'=>4));
		clearTableName($t_info);
		foreach($t_info as $t){
			echo "{$t['alipay']}";
			echo "<br />";
		}
		die();
	}
}

?>