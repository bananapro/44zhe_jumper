<?php

/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/views/default/index.thtml)...
 */
if(@$_GET['proxy']=='en'){ //通过其他域名映射过来的链接，全部转到proxy模块
	$Route->connect('*', array('controller' => 'proxy', 'action' => 'index'));
}else{
	$Route->connect('/', array('controller' => 'default', 'action' => 'index'));
	$Route->connect('/Login', array('controller' => 'login', 'action' => 'index'));
}
?>