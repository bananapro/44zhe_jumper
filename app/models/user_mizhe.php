<?php

class UserMizhe extends AppModel{

    var $name = 'UserMizhe';
    var $useTable = 'user_mizhe';
    var $primaryKey = 'userid';

    function getUser() {

		/*
        if (!$area) {
            $area = getAreaByIp(getip());
        }

        if ($area == '本机地址' || stripos(getip(), '192.168.')!==false) $area = '上海';
		*/
        $user = $this->find(array('status' => 1), '', 'rand()');
        clearTableName($user);
        return $user;
    }
}

?>