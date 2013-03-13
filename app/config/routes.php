<?php

/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/views/default/index.thtml)...
 */
	$Route->connect('/', array('controller' => 'default', 'action' => 'index'));
        
        $Route->connect('/Login', array('controller' => 'login', 'action' => 'index'));

?>