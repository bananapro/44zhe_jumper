<?PHP

	define('DS', '/');
	define('ROOT', dirname(__FILE__) . DS);
	define('API', 'http://www.jumper.com/api/');
	define('DATA', ROOT . 'data' . DS );

	if(is_dir(ROOT . '../../cake/mylibs/'))
		define('MYLIBS', ROOT . '../../cake/mylibs/');
	else
		define('MYLIBS', ROOT . '../cake/mylibs/');



	if(@$_GET['debug']){
		error_reporting( E_ALL & ~E_DEPRECATED);
		ini_set('display_errors', 1);
	}

	require MYLIBS . 'curl.class.php';
	require MYLIBS . '../basics.php';
	require ROOT . '../app/config/bootstrap.php';

	$CURL = new CURL();

	function requestApi($api){

		if(stripos($api, '?')!=false){
			$data = file_get_contents(API . $api .'&debug=false');
		}else{
			$data = file_get_contents(API . $api .'?debug=false');
		}

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
	function finishTask($taskid, $status, $error_msg=''){
		return requestApi('finishWorkerTask/'.$taskid.'/'.$status.'?error_msg='.  urlencode($error_msg));
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
	 * 登陆失败时做记录
	 * @param type $type
	 * @param type $uid
	 */
	function loginFail($type, $uid){

	}

	/**
	 * 登陆成功时清除失败标记
	 * @param type $type
	 * @param type $uid
	 */
	function loginSucc($type, $uid){

	}
?>