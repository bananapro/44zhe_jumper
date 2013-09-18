<?PHP
	define('ROOT', dirname(__FILE__) . '/');
	define('API', 'http://www.jumper.com/api/');

	if(is_dir(ROOT . '../../cake/mylibs/'))
		define('MYLIBS', ROOT . '../../cake/mylibs/');
	else
		define('MYLIBS', ROOT . '../cake/mylibs/');

	define('DS', '/');

	if(@$_GET['debug']){
		error_reporting( E_ALL & ~E_DEPRECATED);
		ini_set('display_errors', 1);
	}

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

	/**
	 * 领取worker任务
	 * @return array
	 */
	function getTask(){
		return requestApi('getWorkerTask');
	}

	/**
	 * worker完成任务后回传状态
	 * @param int $taskid
	 * @param int $status
	 * @return type
	 */
	function finishTask($taskid, $status){
		return requestApi('finishWorkerTask/'.$taskid.'/'.$status);
	}

	/**
	 * 获取所有待完成任务个数
	 * @return type
	 */
	function getTaskTotal(){
		return requestApi('getWorkerTaskTotal');
	}

	/**
	 * 获取跳转用户信息
	 * @param type $type
	 * @param type $uid
	 * @return type
	 */
	function getJumperInfo($type, $uid){
		return requestApi('getJumperInfo/'.$type.'/'.$uid);
	}

	/**
	 *
	 * @param type $type
	 * @param type $uid
	 */
	function loginFail($type, $uid){

	}

	function loginSucc($type, $uid){

	}
?>