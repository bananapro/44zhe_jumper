<?php

class DATABASE_CONFIG {

	var $default = array('driver' => 'mysql',
		'connect' => 'mysql_connect',
		'host' => 'localhost',
		'login' => 'root',
		'password' => 'root',
		'database' => 'jumper',
		'prefix' => '');

	function __construct() {

//		if (strpos(env('SCRIPT_NAME'), 'edm.php')) {
//			$this->default = $this->edm;
//		}

//		if (stripos(env('REQUEST_URI'), '/Order') === 0) {
//			$this->default = $this->order;
//		}
	}

}

?>