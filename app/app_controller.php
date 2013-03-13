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

		require MYCONFIGS . 'order_status.php';
		require MYCONFIGS . 'pay_type.php';
		require MYCONFIGS . 'dispatch_type.php';
		require MYCONFIGS . 'order_source.php';
		require MYCONFIGS . 'order_from.php';
		require MYCONFIGS . 'execute_status.php';
		require MYCONFIGS . 'yesno.php';
		require MYCONFIGS . 'purchase_status.php';
		require MYCONFIGS . 'finance_status.php';
		require MYCONFIGS . 'pickup_status.php';
		require MYCONFIGS . 'shipping_status.php';
		require MYCONFIGS . 'tax_code.php';
		require MYCONFIGS . 'gender.php';
		require MYCONFIGS . 'first_dispatch_type.php';
		require MYCONFIGS . 'commodity_flag.php';
		require MYCONFIGS . 'class_type.php';
		require MYCONFIGS . 'week.php';
		require MYCONFIGS . 'unit.php';
		require MYCONFIGS . 'class_unit.php';
		require MYCONFIGS . 'deliver_type.php';
		require MYCONFIGS . 'cash_type.php';
		require MYCONFIGS . 'commodity_type.php';
		require MYCONFIGS . 'deliver_status.php';
		require MYCONFIGS . 'refund_status.php';
		require MYCONFIGS . 'dispatch_plan_status.php';

		$this->set('ORDER_STATUS', $ORDER_STATUS);
		$this->set('DISPATCH_TYPE', $DISPATCH_TYPE);
		$this->set('PAY_TYPE', $PAY_TYPE);
		$this->set('ORDER_SOURCE', $ORDER_SOURCE);
		$this->set('ORDER_FROM', $ORDER_FROM);
		$this->set('EXECUTE_STATUS', $EXECUTE_STATUS);
		$this->set('YESNO', $YESNO);
		$this->set('PURCHASE_STATUS', $PURCHASE_STATUS);
		$this->set('FINANCE_STATUS', $FINANCE_STATUS);
		$this->set('PICKUP_STATUS', $PICKUP_STATUS);
		$this->set('SHIPPING_STATUS', $SHIPPING_STATUS);
		$this->set('TAX_CODE', $TAX_CODE);
		$this->set('GENDER', $GENDER);
		$this->set('FIRST_DISPATCH_TYPE', $FIRST_DISPATCH_TYPE);
		$this->set('COMMODITY_FLAG', $COMMODITY_FLAG);
		$this->set('CLASS_TYPE', $CLASS_TYPE);
		$this->set('WEEK', $WEEK);
		$this->set('UNIT', $UNIT);
		$this->set('CLASS_UNIT', $CLASS_UNIT);
		$this->set('DELIVER_TYPE', $DELIVER_TYPE);
		$this->set('CASH_TYPE', $CASH_TYPE);
		$this->set('COMMODITY_TYPE', $COMMODITY_TYPE);
		$this->set('DELIVER_STATUS', $DELIVER_STATUS);
		$this->set('REFUND_STATUS', $REFUND_STATUS);
		$this->set('DISPATCH_PLAN_STATUS', $DISPATCH_PLAN_STATUS);

		$this->set('title', '优值供后台管理');
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
		if (!DEBUG||1) {
			if($this->isAjax()||1)
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
