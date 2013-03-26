<?php

class CronController extends AppController {

    var $name = 'Cron';
    var $uses = array('UserFanli');


    function updateUserid($type='fanli'){
        
        if($type == 'fanli'){
            $all_users = $this->UserFanli->findAll(array('userid'=>0, 'status'=>0));
            clearTableName($all_users);
            foreach ($all_users as $u) {
                $p = array();
                $p['username'] = $u['username'];
                $p['type'] = 2;
                $url = ApiFanliPassport('/api/admin/getNameOrId', $p);
                $data = file_get_contents($url);
                $data = json_decode($data, true);
                if($data['status'] == 1 && $data['data']){
                    $this->UserFanli->query("Update user_fanli SET userid={$data['data']}, status=1 WHERE username='{$u['username']}'");
                    echo "{$u['username']} ==> {$data['data']}";
                    br();
                }else{
                    echo "{$u['username']} ==> error!";
                    br();
                }
            }
        }
        echo 'done!';
        br();
        die();
    }

}

?>