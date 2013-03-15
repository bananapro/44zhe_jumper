<?php

class User extends AppModel {
    
    //role (1-大额 2-推手 3-被推)
    function getPoolBig(){
        
        $user = $this->find(array('role'=>1, 'status'=>1), '', 'rand()');
        clearTableName($user);
        return $user;
    }
    
    function getPoolRecommender() {
        
        $user = $this->find(array('role'=>2, 'status'=>1), '', 'rand()');
        clearTableName($user);
        return $user;
    }
    
    function getPoolSpan(){
        
        $area = getAreaByIp(getip());
        $user = $this->find(array('role'=>3, 'status'=>1, 'area'=>$area), '', 'rand()');
        clearTableName($user);
        return $user;
    }
}

?>