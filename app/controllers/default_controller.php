<?php

class DefaultController extends AppController {

	var $name = 'Default';
	var $uses = array('OrderFanli' ,'StatJump');
	var $loginValide = false;
	var $layout = 'ajax';

	function index() {

		$i = 0;
		$fanli = 0;
		$global = array();
		$new2 = array();
		if (isset($_FILES['file'])) {
			$file = file_get_contents($_FILES["file"]["tmp_name"]);

			//去除UTF BOM头
			 if(substr($file,0,3) == pack("CCC",0xef,0xbb,0xbf)){
				 $file = substr($file, 3);
			 }

			$datas = explode("\r\n", trim($file));
			if ($datas) {
				foreach ($datas as $data) {
					$d = explode("\t", trim($data));
					if(!isset($d[9])){
						$message = 'file format error!';
					}else{
						$new = array();
						$new['did'] = $d[0];
						$new['p_id'] = $d[1];
						$new['status'] = $d[2];
						$new['ordernum'] = $d[3];
						$new['p_title'] = $d[4];
						$new['p_price'] = $d[5];
						$new['p_seller'] = $d[6];
						$new['p_yongjin'] = $d[7];
						$new['buydatetime'] = $d[8];
						$new['donedatetime'] = $d[9];
						$new['jumper_uid'] = $d[10];
						$new['p_fanli'] = $new['p_yongjin'] * C('config', 'RATE');
						$new['p_rate'] = C('config', 'RATE');
						$new['buydate'] = date('Y-m-d', strtotime($new['buydatetime']));
						$new['donedate'] = date('Y-m-d', strtotime($new['donedatetime']));
						//去除内部卖家
						if(in_array($new['p_seller'], C('config','HOLD_SELLER'))){
							continue;
						}
						if(intval($new['did'])<1 || intval($new['p_id'])<1){
							continue;
						}

						//关联jump记录
						$date_start = date('Y-m-d', strtotime($new['buydatetime'])-24*3600);
						$date_end = date('Y-m-d', strtotime($new['buydatetime'])+24*3600);
						$hit = $this->StatJump->find("p_id = {$new['p_id']} AND created>'{$date_start}' AND created<'{$date_end}'");

						if($hit){
							clearTableName($hit);
							$global[$new['ordernum']] = $hit['outcode'];
							$global_jumper[$new['jumper_uid']][$new['p_seller']] = $hit['outcode'];
						}

						$new2[] = $new;
					}
				}

				foreach($new2 as $n){

					if(isset($global[$n['ordernum']])){
						$n['outcode'] = $global[$n['ordernum']];
					}else{
						if(isset($global_jumper[$n['jumper_uid']][$n['p_seller']])){
							$n['outcode'] = $global_jumper[$n['jumper_uid']][$n['p_seller']];
						}
					}

					if($n['outcode'] == 'test')continue;

					if(!$this->OrderFanli->find(array('did'=>$n['did'] ,'status'=>$n['status']))){

						if($id = $this->OrderFanli->field('id', array('did'=>$n['did']))){
							$n['id'] = $id;
						}

						$this->OrderFanli->create();
						$this->OrderFanli->save($n);
						$fanli += $n['p_fanli'];
						$i++;
					}
				}
				$fanli = intval($fanli);
				$message = "orders: {$i} fanli: {$fanli} rate: " . C('config', 'RATE')*100 . "%";
			}
			else {
				$message = 'file format error!';
			}
		}
		$this->set('message', $message);
	}
}

?>