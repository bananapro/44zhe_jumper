<?php

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('PS')) {
	define('PS', PATH_SEPARATOR);
}

if (!defined('ROOT')) {

	define('ROOT', (dirname(dirname(__FILE__))));
}
if (!defined('APP_DIR')) {

	define('APP_DIR', 'app');
}

if (!defined('CAKE_CORE_INCLUDE_PATH')) {

	define('CAKE_CORE_INCLUDE_PATH', ROOT);
}

if (!defined('WEBROOT_DIR')) {
	define('WEBROOT_DIR', basename(dirname(__FILE__)));
}
if (!defined('WWW_ROOT')) {
	define('WWW_ROOT', dirname(__FILE__) . DS);
}

//echo 13;die();
if (!defined('CORE_PATH')) {
	if (function_exists('ini_set')) {
		ini_set('include_path', ini_get('include_path') . PS . CAKE_CORE_INCLUDE_PATH .
				PS . ROOT . DS . APP_DIR . DS .
				PS . CAKE_CORE_INCLUDE_PATH . DS . 'cake' . DS . 'libs' . DS . 'view' . DS . 'helpers' .
				PS . ROOT . DS . "mylibs");
		define('APP_PATH', null);
		define('CORE_PATH', null);
	}
	else {
		define('APP_PATH', ROOT . DS . APP_DIR . DS);
		define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
	}
}
//echo CORE_PATH;die();

require CORE_PATH . 'cake' . DS . 'bootstrap.php';

set_time_limit(0);
?>
