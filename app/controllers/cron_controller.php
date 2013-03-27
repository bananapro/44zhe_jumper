<?php

class CronController extends AppController {

    var $name = 'Cron';
    var $uses = array('UserFanli');

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

    function updateUserFanli($type = 'fanli') {

        if ($type == 'fanli') {

            $users = $this->UserFanli->findAll(array('role' => array(1, 2, 3), 'status' => array(1, 3)));
            $userids = fieldSet($users, 'userid');
            //对用户进行分段，每50个一组
            $page = ceil(count($userids) / 50);
            for ($i = 0; $i < $page; $i++) {
                $new = array_slice($userids, $i*2, 50);
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

}

?>