<?php
class User extends AppModel {

    //role (1-大额 2-推手 3-被推)
    function getPoolBig($area = ''){

        if(!$area){
            $area = getAreaByIp(getip());
        }
        $user = $this->find(array('role'=>1, 'status'=>1, 'area'=>$area), '', 'rand()');
        clearTableName($user);
        return $user;
    }

    function getPoolRecommender() {

        $user = $this->find(array('role'=>2, 'status'=>1), '', 'rand()');
        clearTableName($user);
        return $user;
    }

    function getPoolSpan($area = '', $level=1){

		//level 1 - 没用过的推手(用于直接给金额大于30的跳转)
		//level 2 - 用过的推手(用户给金额小于30的跳转)
        if(!$area){
            $area = getAreaByIp(getip());
        }

		if($level==1){
			$user = $this->query("SELECT * FROM user_fanli WHERE status=1 AND role=3 AND area='{$area}' AND pause_date='0000-00-00 00:00:00' LIMIT 1");
		}else{
			$user = $this->query("SELECT * FROM user_fanli WHERE status=1 AND role=3 AND area='{$area}' AND pause_date>'2013-04-01' LIMIT 1");
		}

        clearTableName($user);
        return $user[0];
    }
}

?>