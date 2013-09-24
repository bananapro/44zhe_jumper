<?PHP

//领取任务
require './common.php';

$total_jobs = getTaskTotal();
$page = <<<EOT
<html>
<title>Work任务自动处理</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<body>
<div style="text-align:center;width:100%">
<br /><br /><br /><br /><br /><br /><br />
<h2><span id="title">Worker待处理任务数<b>{$total_jobs}</b> ，计时：<span id="count">30</span>s</span>，<a href="javascript:void(0)" onclick="clearInterval(q)">暂停</a></h2>
</div>
</body></html>
<script>
 var q = setInterval(function(){
if(parseInt(document.getElementById('count').innerHTML) == 0){
	location.reload();
	clearInterval(q);
	document.getElementById('title').innerHTML = '任务正在处理中 ...';
	is_working = true;
}else{
	document.getElementById('count').innerHTML = parseInt(document.getElementById('count').innerHTML) - 1;
}}, 1000);
</script>
EOT;

echo $page;
ignore_user_abort(1);
fastcgi_finish_request();

$t_info = getTask();
if ($t_info) {
	require_once MYLIBS . 'jumper' . DS . "jtask_{$t_info['jumper_type']}.class.php";
	$obj_name = 'Jtask' . ucfirst($t_info['jumper_type']);
	$task = new $obj_name($t_info);
	$status = $task->workerConvertLink();
	finishTask($t_info['id'], $status, $task->error_msg);
}
?>