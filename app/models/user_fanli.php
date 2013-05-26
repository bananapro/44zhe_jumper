<?php
require_once 'user.php';

class UserFanli extends User {

    var $name = 'UserFanli';
    var $useTable = 'user_fanli';
    var $primaryKey = 'userid';

	//role (1-大额 2-推手 3-被推 4-商城)
    function getPoolBig($area = ''){

        if(!$area){
            $area = getAreaByIp();
			if ($area == '本机地址' || stripos(getip(), '192.168.')!==false) $area = '上海';
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
            $area = getAreaByIp();
			if ($area == '本机地址' || stripos(getip(), '192.168.')!==false) $area = '上海';
        }

		if($level==1){
			$user = $this->query("SELECT * FROM user_fanli WHERE status=1 AND role=3 AND area='{$area}' AND pause_date='0000-00-00 00:00:00' LIMIT 1");
		}else{
			$user = $this->query("SELECT * FROM user_fanli WHERE status=1 AND role=3 AND area='{$area}' AND pause_date>'2013-04-01' LIMIT 1");
		}

        clearTableName($user);
        return $user[0];
    }

	function getShopUser($shop, $my_user){

		if(!$shop || !$my_user)return;

		$users = $this->findAll(array('role'=>4, 'status'=>1));
		clearTableName($users);

		$hit = false;
		foreach($users as $u){

			if($u['shopmark']){
				$u['shopmark'] = unserialize($u['shopmark']);
			}else{
				$u['shopmark'] = array();
			}

			if(time() - @$u['shopmark'][$shop]['time'] > C('config', 'SHOP_JUMP_DS_TIME') || $my_user == @$u['shopmark'][$shop]['my_user']){
				$hit = true;
				$u['shopmark'][$shop] = array('my_user'=>$my_user, 'time'=>time());
				//深圳用户白名单，不计入商城跳转
				$area = getAreaByIp();
				if($area != '深圳'){
					$this->save(array('userid'=>$u['userid'], 'shopmark'=>serialize($u['shopmark'])));
				}
				return $u['userid'];
			}
		}

		return false;
	}
}

?>