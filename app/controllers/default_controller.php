<?php
class DefaultController extends AppController {

	var $name = 'Default';
	var $uses = array();

	function index(){
                $this->layout = 'frame';
                $this->set('r', @$_GET['r']);
	}


	function menu(){

	}

	function top(){
	        $this->layout = 'dialog';
	}

	function home(){
	}
}
?>