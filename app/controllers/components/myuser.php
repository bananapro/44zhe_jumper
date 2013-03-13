<?php

class MyuserComponent extends Object {

	var $controller = true;
	var $components = Array('Session');
	var $permission = '';

	function startup(&$controller) {
		$this->controller = $controller;
		if ($this->controller->loginValide) {
			require_once MYLIBS . "permission.class.php";
			$this->permission = new permission();
		};
	}

	function &getInstance() {

		static $instance = array();

		if (!$instance) {
			$instance[0] = new MyuserComponent();
		}
		return $instance[0];
	}

	function signIn(&$admin) {

		//更新session
		$this->Session->write('userinfo', $admin['Admin']);
		$this->Session->write('userinfo.islogined', 1);

		setcookie('login_name', $admin['Admin']['name'], time() + 2 * 30 * 24 * 60 * 60, '/');
		$data = file_get_contents('/data/vhosts/backend_web/logs/login.log');
		$data = "[" . date('Y-m-d H:i:s') . "][Admin:{$admin['Admin']['name']}][" . getip() . "][login]\n" . $data;
		file_put_contents('/data/vhosts/backend_web/logs/login.log', $data);
		return true;
	}

	function resignIn() {

		$admin = $this->controller->Admin->findById($this->getID());
		if (!$admin)
			return false;
		$this->sigOut();
		return $this->signIn($admin);
	}

	function sigOut() {
		$this->Session->del('userinfo');
	}

	//  get user proper
	function getID() {
		return $this->Session->read('userinfo.id');
	}

	function getName() {
		return $this->Session->read('userinfo.name');
	}

	function getTel() {
		return $this->Session->read('userinfo.tel');
	}


	function getEmail() {
		return $this->Session->read('userinfo.email');
	}

	function getRoleId() {
		return $this->Session->read('userinfo.role_id');
	}

	function getStoreId() {
		return $this->Session->read('userinfo.store_id');
	}

	function isLogin() {
		if (!$this->Session->read('userinfo.islogined'))
			return false;
		return true;
	}

	function getAcessmask($group_id) {
		return $this->permission->getAcessmask($this->getRoleId(), $group_id);
	}

	function checkPermission($group_id, $permission_id) {

		$mask = $this->getAcessmask($group_id);
		return $this->permission->checkPermission($mask, $permission_id);
	}

	function checkGroupPermission($group_id) {

		return $this->permission->checkGroupPermission($this->getRoleId(), $group_id);
	}

}

?>