<?php

class StatApi extends AppModel {

	var $name = 'StatApi';
	var $useTable = 'stat_api';

	function add($status=1, $code='', $p_id='', $content='', $key=''){

		$this->create();
		$this->save(array('status'=>$status, 'code'=>$code, 'p_id'=>$p_id, 'content'=>$content, 'ip'=>getIp(), 'key'=>$key));
	}
}

?>