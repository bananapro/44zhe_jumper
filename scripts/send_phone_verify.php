<?php

    //'passport:verifykey:' . session_id() . ':userid:' . $userid . ':phone:' . $sPhone . ':pos:' . $sPos;
    //pos(601,602,603,1301,802,803,903,605,604,606,906,907)

    $sid = $argv[1];
    $uid = $argv[2];
    $phone = $argv[3];

    $redis = new Redis();
    $redis->connect('192.168.2.165', 6381, 10);

    $pos = array(601,602,603,1301,802,803,903,605,604,606,906,907);
    foreach($pos as $p){

        $code = $redis->get('passport:verifykey:' . $sid . ':userid:' . $uid . ':phone:' . $phone . ':pos:' . $p);
        if($code){
            echo $code . "\n";
        }
    }
?>