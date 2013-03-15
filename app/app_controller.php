<?php

class AppController extends Controller {

	var $components = array('Pagination', 'Mytools', 'Myuser');
	var $helpers = array('Pagination', 'Javascript', 'Global', 'Ajax');
	var $loginValide = 1;

	function beforeFilter() {
		parent::beforeFilter();

		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

		if ($this->loginValide && !$this->Myuser->isLogin()) {
			if ($this->action == 'index' && $this->name == 'Default')
				$this->redirect('/Login');
			else
				$this->flash('您未登陆后台，或者时间超时，请重新登陆', '/Login?r=' . urlencode($_SERVER['REQUEST_URI']), 1);
		}
	}

	function beforeRender() {
		parent::beforeRender();

		$this->set('YESNO', C('yesno'));

		$this->set('title', 'jumper');
		$this->set('myuser', $this->Myuser);
		$this->setAjax();
	}

	//自动识别ajax
	function setAjax() {
		if ($this->isAjax()) {
			$this->layout = 'ajax';

			// Add UTF-8 header for IE6 on XPsp2 bug
			header('Content-Type: text/html; charset=UTF-8');
		}
	}

	function isAjax() {
		if (env('HTTP_X_REQUESTED_WITH') != null) {
			return env('HTTP_X_REQUESTED_WITH') == "XMLHttpRequest";
		}
		else {
			return false;
		}
	}

	function setFlash($msg, $status = 0) {
		$_SESSION['Message']['flash'] = $msg;
		$_SESSION['Message']['flash_status'] = $status;
	}

	function checkFlash() {
		if (isset($_SESSION['Message']['flash']) && $_SESSION['Message']['flash'])
			return true;
		else
			return false;
	}

	function _success($message='') {

		if($message==='')$message = '操作成功!';
		if (!DEBUG) {
			if($this->isAjax())
				echo json_encode(array('message' => $message, 'status' => 1));
			else
				$this->flash ($message, '/Order', 3);
		}
		else {
			pr(array('message' => $message, 'status' => 1));
		}
		die();
	}

	function _error($message='') {

		if(!$message)$message = '系统发生错误，请重试!';
		if (!DEBUG) {
			if($this->isAjax())
				echo json_encode(array('message' => $message, 'status' => 0));
			else
				$this->flash ($message, '/', 3);
		}
		else {
			pr(array('message' => $message, 'status' => 0));
		}
		die();
	}

}

?>
