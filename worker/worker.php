<?PHP
	//领取任务
	require './common.php';

	$t_info = getTask();
	if($t_info){
		require_once MYLIBS . 'jumper' . DS . "jtask_{$t_info['jumper_type']}.class.php";
		$obj_name = 'Jtask'.ucfirst($t_info['jumper_type']);
		$task = new $obj_name($t_info);
		$status = $task->workConvertLink();
		finishTask($t_info['id'], $status);
	}


	$total_jobs = getTaskTotal();
	echo <<<EOT
<br /><br /><br />
<h2>Worker待处理 <b><?=$total_jobs?></b>，跳转倒数 <span id="count">60</span></h2>
<script>
     var q = setInterval(function(){
	document.getElementById('count').innerHTML = parseInt(document.getElementById('count').innerHTML) - 1;
	if(parseInt(document.getElementById('count').innerHTML) == 0){
	    location.reload();
	}
    }, 1000);
</script>
EOT;

?>