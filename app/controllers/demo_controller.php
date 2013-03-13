<?php

class DemoController extends AppController {

    var $loginValide = 0;
    var $name = 'Demo';
    var $uses = array();
    var $page_show = 20;
    var $page_sortBy = 'id';
    var $page_direction = 'DESC';
    var $page_maxPages = 10;

    function beforeFilter() {

        parent::beforeFilter();
    }

    function beforeRender() {
        parent::beforeRender();
    }

    function index() {
        echo 'hello';
        die();
    }

}

?>