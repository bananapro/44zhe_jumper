<?php

class AppController extends Controller {

    var $components = array('Pagination', 'Mytools', 'Myuser');
    var $helpers = array('Pagination', 'Javascript', 'Global', 'Ajax');
    var $loginValide = 0;

    function beforeFilter() {
        parent::beforeFilter();

        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        header("P3P: CP=CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR");

        if ($this->loginValide && !$this->Myuser->isLogin()) {
            if ($this->action == 'index' && $this->name == 'Default')
                $this->redirect('/Login');
            else
                $this->flash('您未登陆后台，或者时间超时，请重新登陆', '/Login?r=' . urlencode($_SERVER['REQUEST_URI']), 1);
        }
    }

    function beforeRender() {
        parent::beforeRender();

        $this->set('YESNO', C('yesno'));

        $this->set('title', 'jumper');
        $this->set('myuser', $this->Myuser);
        $this->setAjax();
    }

    //自动识别ajax
    function setAjax() {
        if ($this->isAjax()) {
            $this->layout = 'ajax';

            // Add UTF-8 header for IE6 on XPsp2 bug
            header('Content-Type: text/html; charset=UTF-8');
        }
    }

    function isAjax() {
        if (env('HTTP_X_REQUESTED_WITH') != null) {
            return env('HTTP_X_REQUESTED_WITH') == "XMLHttpRequest";
        } else {
            return false;
        }
    }

    function isJsonp() {
        $jsoncallback = isset($_REQUEST['jsoncallback']) ? $_REQUEST['jsoncallback'] : '';
        return $jsoncallback;
    }

    function setFlash($msg, $status = 0) {
        $_SESSION['Message']['flash'] = $msg;
        $_SESSION['Message']['flash_status'] = $status;
    }

    function checkFlash() {
        if (isset($_SESSION['Message']['flash']) && $_SESSION['Message']['flash'])
            return true;
        else
            return false;
    }

    function _success($message = '', $force_api = false) {

        if ($message === '')
            $message = '操作成功!';
        if (!DEBUG) {
            if ($this->isAjax() || $this->isJsonp() || $force_api) {

                if ($this->isJsonp()) {
                    $this->_jsonpReturn($message, 1);
                } else {
                    echo json_encode(array('message' => $message, 'status' => 1));
                }
            } else {
                $this->flash($message, '/', 3);
            }
        } else {
            pr(array('message' => $message, 'status' => 1));
        }
        die();
    }

    function _error($message = '', $force_api=false) {

        if (!$message)
            $message = '系统发生错误，请重试!';
        if (!DEBUG) {
            if ($this->isAjax() || $force_api) {

                if ($this->isJsonp()) {
                    $this->_jsonpReturn($message, 0);
                } else {
                    echo json_encode(array('message' => $message, 'status' => 0));
                }
            } else {
                $this->flash($message, '/', 3);
            }
        } else {
            pr(array('message' => $message, 'status' => 0));
        }
        die();
    }

    /**
     * jsonp格式返回数据，登录时跨域post提交用
     * @param array $data 需返回的数据数组
     * @param string $info 需返回的信息
     * @param int $status 需返回的状态
     */
    function _jsonpReturn($message = '', $status = '') {
        header("Content-Type:application/x-javascript; charset=utf-8");
        echo htmlentities($_REQUEST['jsoncallback']) . '(' . json_encode(array('message' => $message, 'status' => $status)) . ')';
        exit;
    }


	/*
	 * 记录跳转日志
	 */
	function _addStatJump($shop, $jumper_type, $my_user, $outcode, $userid, $p_id='', $p_title='', $p_price='', $p_fanli='', $p_seller=''){
		//记录跳转日志
		$stat = array();
		$stat['p_id'] = $p_id;
		$stat['p_title'] = $p_title;
		$stat['p_price'] = $p_price;
		$stat['p_fanli'] = $p_fanli;
		$stat['p_seller'] = $p_seller;
		$stat['ip'] = getip();
		$stat['area'] = getAreaByIp();
		$stat['shop'] = $shop;
		$stat['jumper_uid'] = $userid;
		$stat['jumper_type'] = $jumper_type;
		$stat['my_user'] = urldecode($my_user);
		$stat['outcode'] = $outcode;
		$stat['target'] = @$_GET['target'];
		$stat['client'] = getBrowser();
		$stat['source'] = @$_SESSION['source'];
		foreach ($stat as $k => $v) {
			if (!$v)
				unset($stat[$k]);
		}

		$this->StatJump->create();
		$this->StatJump->save($stat);
	}
}

?>
