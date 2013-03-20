<?php

class DefaultController extends AppController {

    var $name = 'Default';
    var $uses = array();
    var $loginValide = false;
    var $layout = 'ajax';

    function index(){
        echo 'hello';
        die();
    }
}

?>