<?PHP

//领取任务
require './common.php';

//http://api.duo.com/getJob/notifyOrderBackSms
//http://api.duo.com/getJob/notifyPaymentCompleteSms

if(@$_SESSION['duosq_jobs']){
	$total_jobs = count($_SESSION['duosq_jobs']);
}else{
	$jobs_order = requestApiDuosq('getJob/notifyOrderBackSms');
	$jobs_pay = requestApiDuosq('getJob/notifyPaymentCompleteSms');
	if(!$jobs_order)$jobs_order = array();
	if(!$jobs_pay)$jobs_pay = array();

	$jobs = array_merge($jobs_order, $jobs_pay);
	$total_jobs = intval(count($jobs));
	if($total_jobs){
		$_SESSION['duosq_jobs'] = $jobs;
	}
}
$test_msg = '测试发送';
$ret = file_get_contents('http://192.168.10.1:9618/User=duosq,Password=duosq,MsgID=1,Phone=18666660880,Msg='.g2u($test_msg, true));

if($ret === '00'){
	$page = <<<EOT
<html>
<title>多省钱短信发送任务自动处理</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<body>
<div style="text-align:center;width:100%">
<br /><br /><br /><br /><br /><br /><br />
<h2><span id="title">待处理短信任务数<b>{$total_jobs}</b></h2>
</div>
</body></html>
EOT;

	if($_SESSION['duosq_jobs']){
		foreach($_SESSION['duosq_jobs'] as $key => $job){
			$ret = file_get_contents('http://127.0.0.1:9618/User=duosq,Password=duosq,MsgID=1,Phone='.$job['mobile'].',Msg='.urlencode(g2u($job['content'], true)));

			if($ret === '00'){
				unset($_SESSION[$key]);
				usleep(200);
			}else{
				echo '<html><title>多省钱短信发送任务自动处理</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><body>';
				echo "[{$ret}]{$job['mobile']}[{$job['content']}][failed]";
				echo '</body></html>';
				die();
			}
		}
	}

	echo $page;
}else{
	echo 'can not connect to sms server';
}

?>