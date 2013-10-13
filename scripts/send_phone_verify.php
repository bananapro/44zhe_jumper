<?php
//通过第三方接口转换安全手机地址

while(1){

    //'passport:verifykey:' . session_id() . ':userid:' . $userid . ':phone:' . $sPhone . ':pos:' . $sPos;
    //pos(601,602,603,1301,802,803,903,605,604,606,906,907)


    //最后一次完成任务后90分钟内均变为15秒刷新一次任务，否则10分钟刷一次
    $task = file_get_contents('http://go.44zhe.com/api/doSmsCodeTask/get');
    if($task && $task != 'empty'){
        list($sid, $uid, $mobile) = explode("|", $task);
        if($sid && $uid && $mobile){
            $redis = new Redis();
            $redis->connect('192.168.2.165', 6381, 10);

            $pos = array(601,602,603,1301,802,803,903,605,604,606,906,907);
            $get_code = array();
            foreach($pos as $p){

                $code = $redis->get('passport:verifykey:' . $sid . ':userid:' . $uid . ':phone:' . $mobile . ':pos:' . $p);
                if($code){
                    $get_code[] = $code;
                }
            }

            $redis->close();

            if($get_code){
                $return = file_get_contents('http://go.44zhe.com/api/doSmsCodeTask/set/'.join('|', $get_code));
                if($return == 'ok'){
                    @file_put_contents('/tmp/last_hit.time', time());
                    echo date("[Y-m-d H:i][$mobile]").'[hit code]';
                    echo "\n";
                }else{
                    echo date("[Y-m-d H:i][$mobile]").'[save code error]';
                    echo "\n";
                }

            }else{
                echo date("[Y-m-d H:i][$mobile]").'[none code]';
                echo "\n";
            }
        }
    }else{
        echo date("[Y-m-d H:i]").'[none task]';
        echo "\n";
    }

    if(time() - @file_get_contents('/tmp/last_hit.time') < 90*60){
        sleep(10);
    }else{
        sleep(10*60);
    }

}
?>