<?php
class UserBind extends AppModel {

	var $name = 'UserBind';
	var $useTable = 'user_bind';
	static $m;

	function getJumper($my_user = ''){

		if($my_user){

			if($my_user != 'selinadaisy@126.com'){

				//判断账号是否已绑过，如果作废，着解绑
				$user = $this->find(array('my_user'=>$my_user, 'status'=>1));
				if($user){
					clearTableName($user);
					$m = $this->getChannelUserM($user['jumper_type']);
					$jumper = $m->find(array('userid'=>$user['jumper_uid']));
					clearTableName($jumper);
					if($jumper['status'] == 1){
						$jumper['type'] = $user['jumper_type'];
						return $jumper;
					}else{
						//解绑用户
						$this->unbindUser($my_user, $user['jumper_type'], $user['jumper_uid']);
					}
				}

				return $this->bindUser($my_user);
			}else{

				return $this->bindUser($my_user, true);
			}

		}else{

			return $this->getAnonUser();
		}
	}

	//绑定策略: 固化账号渠道分配(IP)，支持优渠道先级调整，按渠道前10个账号均分
	function bindUser($my_user, $rand = false){

		if(!$my_user)return;
		$ip = getip();
		//从C段IP固定渠道
		$ip_c = getIpByLevel('c');
		$ip_c = md5($ip_c);
		$total_rate = 0;
		$channel_rebin = array();
		$channel_all = array();

		foreach(C('config', 'JUMP_CHANNEL') as $channel => $rate){

			$m = $this->getChannelUserM($channel);
			//跳过无账号的渠道
			if(!$rand){
				if(!$m->find(array('bind_count'=>'< '.C('config','JUMP_CHANNEL_BIND_LIMIT'), 'status'=>1)))
					continue;
			}

			$total_rate += $rate;
			for($i=0; $i<$rate;$i++){
				$channel_rebin[] = $channel;
				$channel_all[$channel] = 1;
			}
		}

		if(!$total_rate){
			alert('bind', 'account empty '.$my_user);
			return;//无有效渠道账号
		}

		$channel = $channel_rebin[hexdec($ip_c[0].$ip_c[1])%$total_rate];

		//每次均分前10个账号
		if(!$rand){
			$users = $this->getChannelUserM($channel)->findAll(array('status' => 1, 'bind_count'=>'< '.C('config','JUMP_CHANNEL_BIND_LIMIT')), '', 'created asc', 10);
		}else{
			$users = $this->getChannelUserM($channel)->findAll(array('status' => 1), '', 'rand()', '', 10);
		}

		clearTableName($users);
		shuffle($users);
		$selected = array_pop($users);
		$selected['type'] = $channel;

		//如果此次绑定是临时随机，用于某些渠道临时故障，或是系统账号
		if($rand)return $selected;

		if($id = $this->field('id', array('my_user'=>$my_user, 'jumper_type'=>$channel, 'jumper_uid'=>$selected['userid']))){
			$this->save(array('id'=>$id, 'status'=>1));
		}else{
			$this->create(); //防止先unbindUser，导致id已有数据，此处会变为update
			$this->save(array('my_user'=>$my_user, 'jumper_type'=>$channel, 'jumper_uid'=>$selected['userid']));
		}

		$this->updateBindCount($channel, $selected['userid']);


		LogInfo("{$my_user} bind to [{$selected['type']}][{$selected['userid']}]");

		return $selected;
	}

	function unbindUser($my_user, $jumper_type, $jumper_uid){

		$id = $this->field('id', array('my_user'=>$my_user, 'jumper_type'=>$jumper_type, 'jumper_uid'=>$jumper_uid));
		if($id){
			$this->save(array('id'=>$id, 'status'=>0));
			$this->updateBindCount($jumper_type, $jumper_uid);
		}
		return true;
	}

	//临时，按IP随机返回固定渠道用户，并没绑定用户，防止感染正常用户
	function getAnonUser(){

		$ip_c = getIpByLevel('c');
		$ip_c = md5($ip_c);
		$channel = hexdec($ip_c[0].$ip_c[1])%count(C('config', 'JUMP_CHANNEL_ENABLE'));
		$a = C('config', 'JUMP_CHANNEL_ENABLE');
		$channel = $a[$channel];
		$selected = $this->getChannelUserM($channel)->find(array('allow_anon'=>1), '', 'rand()');
		$selected['type'] = $channel;

		return $selected;
	}

	//更新用户被绑定总量
	function updateBindCount($jumper_type, $jumper_uid){
		if(!$this->getChannelUserM($jumper_type)->find(array('userid'=>$jumper_uid))){
			return;
		}
		$count = $this->findCount(array('status'=>1, 'jumper_type'=>$jumper_type, 'jumper_uid'=>$jumper_uid));
		$this->getChannelUserM($jumper_type)->save(array('userid'=>$jumper_uid, 'bind_count'=>$count));
	}

	//按渠道返回Model
	function getChannelUserM($type){

		if(isset(self::$m[$type])){

			return self::$m[$type];

		}else{

			$class_name = 'User'.ucfirst($type);
			if(class_exists($class_name)){
				self::$m[$type] = new $class_name;
				return self::$m[$type];
			}
		}

	}
}

?>