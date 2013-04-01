<?php

class CronController extends AppController {

    var $name = 'Cron';
    var $uses = array('UserFanli');

    //每日执行一次，更新用户的ID
    function updateUserid($type = 'fanli') {

        if ($type == 'fanli') {
            $all_users = $this->UserFanli->findAll(array('userid' => 0, 'status' => 0));
            clearTableName($all_users);
            foreach ($all_users as $u) {
                $p = array();
                $p['username'] = $u['username'];
                $p['type'] = 2;
                $data = file_get_contents(ApiFanliPassport('/api/admin/getNameOrId', $p));
                $data = json_decode($data, true);
                if ($data['status'] == 1 && $data['data']) {
                    $this->UserFanli->query("Update user_fanli SET userid={$data['data']}, status=1 WHERE username='{$u['username']}'");
                    echo "{$u['username']} ==> {$data['data']}";
                    br();
                } else {
                    echo "{$u['username']} ==> error!";
                    br();
                }
            }
        }
        echo 'done!';
        br();
        die();
    }

    //每日执行一次，更新用户返利网的资产
    function updateUserFanli($type = 'fanli') {

        if ($type == 'fanli') {

            //$users = $this->UserFanli->findAll(array('role' => array(1, 2, 3), 'status' => array(1, 3)));
            $weekdate = date('Y-m-d', time()-7*24*3600);
            $users = $this->UserFanli->findAll("(role IN(1,2,3) AND status IN(1,3)) OR (status = 2 AND ts > '{$weekdate}')");
            
            $userids = fieldSet($users, 'userid');
            //对用户进行分段，每50个一组
            $page = ceil(count($userids) / 30);
            for ($i = 0; $i < $page; $i++) {
                $new = array_slice($userids, $i*30, 30);
                $ids = join($new, ',');
                $p = array();
                $p['userid'] = $ids;
                
                $data = file_get_contents(ApiFanliPassport('/api/admin/userAccountBalance', $p));
                $data = json_decode($data, true);
                if ($data['status'] == 1 && $data['data']) {
                    foreach ($data['data'] as $userid => $info) {
                        $this->UserFanli->save(array('userid' => $userid, 'fl_cash' => $info['fanli_yuan'], 'fl_fb' => $info['jifen']));
                        echo "{$userid} => cash:{$info['fanli_yuan']} FB:{$info['jifen']}";
                        br();
                    }
                }
            }


            echo 'done!';
            die();
        }
    }
    
    //每月执行一次，更新10位推荐人
    function updateRecommender($n = 10){
        
        $this->UserFanli->query("UPDATE user_fanli SET status=2 WHERE status=1 AND role=2");
        for($i=1; $i<=$n; $i++){
            //取出干净的会员
            $u = $this->UserFanli->find(array('role'=>0, 'status'=>1), '', 'rand()');
            if($u){
                clearTableName($u);
                $this->UserFanli->save(array('userid'=>$u['userid'], 'role'=>2));
                echo "[{$u['area']}] {$u['userid']} become recommender";
            }else{
                echo "can not find a clear user";
            }
            
            br();
        }
        
        echo 'done!';
        br();
        die();
    }
    
    //每周执行一次，从被推池按地区均匀抽出10人作为大池
    function updateBig($n = 10){
        
        //$this->UserFanli->query("UPDATE user_fanli SET status=2 WHERE status=1 AND role=1");
        
        $date = date('Y-m-d', time()-30*24*3600);
        $area = $this->UserFanli->query("SELECT count(*) nu, area FROM user_fanli WHERE created>'{$date}' GROUP BY area");
        clearTableName($area);
        $total = 0;
        foreach($area as $a){
            $total += $a['nu'];
        }
        
        $num = array();
        foreach($area as $a){
            $num[$a['area']] = ceil($n*$a['nu']/$total);
        }
        
        foreach($num as $area=>$n){
            
            for($i=1; $i<=$n; $i++){
                //取出干净的被推会员
                $u = $this->UserFanli->getPoolSpan($area);
                if($u){
                    $this->UserFanli->save(array('userid'=>$u['userid'], 'role'=>1));
                    echo "[{$area}] {$u['userid']} become big";
                }else{
                    echo "[{$area}] can not find a span user";
                }

                br();
            }
        }
        
        die();
    }

}

?>