<?php

class StatApi extends AppModel {

    var $name = 'StatApi';
    var $useTable = 'stat_api';

    function add($status=1, $code='', $p_id='', $content=''){

    	$this->create();
    	$this->save(array('status'=>$status, 'code'=>$code, 'p_id'=>$p_id, 'content'=>$content, 'ip'=>getip()));
    }
}

?>