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
        $user = $this->find(array('role'=>1, 'status'=>1), '', 'rand()');
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


		//查看最后跳转的用户
		foreach($users as $u){
			if($u['shopmark']){
				$u['shopmark'] = unserialize($u['shopmark']);
			}

			if($u['shopmark'] && $my_user == @$u['shopmark'][$shop]['my_user']){

				$u['shopmark'][$shop] = array('my_user'=>$my_user, 'time'=>time());
				$this->save(array('userid'=>$u['userid'], 'shopmark'=>serialize($u['shopmark'])));
				return $u['userid'];
			}

		}


		foreach($users as $u){

			if($u['shopmark']){
				$u['shopmark'] = unserialize($u['shopmark']);
			}else{
				$u['shopmark'] = array();
			}

			if(time() - @$u['shopmark'][$shop]['time'] > C('config', 'SHOP_JUMP_DS_TIME')){

				$u['shopmark'][$shop] = array('my_user'=>$my_user, 'time'=>time());
				//深圳用户白名单，不计入商城跳转

				$area = getAreaByIp();
				if($area != '深圳'){
					$return = $this->save(array('userid'=>$u['userid'], 'shopmark'=>serialize($u['shopmark'])));
				}
				return $u['userid'];
			}
		}

		return false;
	}


	//15天以前被暂停的推手，如果账户无资产，则恢复身份并清空pause_date
	//如果有资产，则查询order判断购物金额是否大于30，如果小于30则重新恢复身份
	function doRestore() {

		$weekdate = date('Y-m-d', time() - 12 * 24 * 3600);
		$weekdate2 = date('Y-m-d', time() - 26 * 24 * 3600);
		$this->query("UPDATE user_fanli SET status=1, pause_date='0000-00-00 00:00:00' WHERE role=3 AND status = 2 AND pause_date < '{$weekdate}' AND fl_fb=0");
		$users = $this->findAll("role=3 AND status = 2 AND pause_date < '{$weekdate}' AND pause_date > '{$weekdate2}' AND fl_fb>0");

		clearTableName($users);
		foreach ($users as $user) {

			if ($nu = $this->query("SELECT sum(p_price) as nu FROM order_fanli WHERE jumper_uid='{$user['userid']}'")) {
				$nu = @intval($nu[0][0]['nu']);
				if ($nu < 30) {
					$this->save(array('userid' => $user['userid'], 'status' => 1));
				}
			};
		}
	}
}

?>