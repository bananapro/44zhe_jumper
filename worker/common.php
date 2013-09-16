<?PHP


	define('ROOT', dirname(__FILE__) . '/');
	define('API', 'http://go.44zhe.com/api/');
	define('DS', '/');

	require ROOT . 'common_env.php';
	require MYLIBS . 'curl.class.php';
	require MYLIBS . '../basics.php';
	require ROOT . '../app/config/bootstrap.php';

	$CURL = new CURL();

	function requestApi($api){

		$data = file_get_contents(API . $api .'?debug=false');
		if($data){
			$data = json_decode($data, true);
			if($data['status'] == 1){
				return $data['message'];
			}
		}
		return false;
	}

	function getTask(){
		return requestApi('getWorkerTask');
	}

	function finishTask($taskid, $status){
		return requestApi('finishWorkerTask/'.$taskid.'/'.$status);
	}

	function getTaskTotal(){
		return requestApi('getWorkerTaskTotal');
	}

	function getJumperInfo($type, $uid){
		return requestApi('getJumperInfo/'.$type.'/'.$uid);
	}

	function workLoginNeed($type, $uid){

	}

	function workLoginSucc($type, $uid){

	}
?>