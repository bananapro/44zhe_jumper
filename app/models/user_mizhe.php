<?php

class UserMizhe extends AppModel{

    var $name = 'UserMizhe';
    var $useTable = 'user_mizhe';
    var $primaryKey = 'userid';

    function getUser($area='') {
        
        if (!$area) {
            $area = getAreaByIp(getip());
        }

        if ($area == '本机地址') $area = '上海';
        $user = $this->find(array('status' => 1, 'area' => $area), '', 'rand()');
        clearTableName($user);
        return $user;
    }
}

?>