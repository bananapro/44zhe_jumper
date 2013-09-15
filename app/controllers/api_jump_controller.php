<?php

class ApiJumpController extends AppController {

	var $name = 'ApiJump';
	var $uses = array('UserFanli', 'UserMizhe', 'UserCandidate', 'StatJump', 'StatRegFailed', 'StatJump', 'OrderFanli');
	var $layout = 'ajax';

	function demoReg() {

	}

	function demoJump($driver='51fanli') {
		$this->set('driver', $driver);
	}

	function alert($target, $info) {

		alert($target, $info);
		if (@$_GET['u']) {
			$this->redirect($_GET['u']);
		}
		else {
			$this->redirect(DEFAULT_ERROR_URL);
		}
	}

	/**
	 * 客户端请求返利网接口出错时，强制进行s2s端的跳转
	 *
	 * http://go.44zhe.com/api/jumpForce/taobao/bluecone@163.com/18484876328/0.11/0.01
	 * @param type $shop
	 * @param type $my_user
	 * @param type $p_id
	 * @param type $p_price
	 * @param type $p_fanli
	 */
	function jumpForce($shop, $my_user, $p_id='', $p_price='', $p_fanli='') {

		$data = file_get_contents('http://fun.51fanli.com/api/search/getItemById?pid=' . $p_id . '&is_mobile=2&shoptype=2&track_code=a');
		if ($data) {
			$data = json_decode($data, true);
			if ($data['status']) {
				$_GET['ju'] = $data['data']['url'];
				$_GET['p_seller'] = $data['data']['shopname'];
				$_GET['p_title'] = $data['data']['title'];
			}
			else {
				alert('jumpForce', 'status error');
				$this->redirect(DEFAULT_ERROR_URL);
			}
		}
		else {
			alert('jumpForce', 'can not fetch data');
			$this->redirect(DEFAULT_ERROR_URL);
		}

		$this->jump($shop, $my_user, $p_id, $p_price, $p_fanli);
	}

}

?>