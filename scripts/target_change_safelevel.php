<?php

require './common.php';
$api = ApiFanliPassport('/api/admin/updateBank', array('userid' => $user['userid'], 'pay_method' => 2, 'pay_account' => $user['alipay'], 'ip' => '127.0.0.1'));
?>
