<?php
class User extends AppModel {

	function getUser() {

        $user = $this->find(array('status' => 1), '', 'rand()');
        clearTableName($user);
        return $user;
    }
}

?>