<?php

class ApiController extends AppController {

    var $name = 'Api';
    var $uses = array('UserFanli', 'UserCandidate', 'JumpStat');
    var $layout = 'ajax';

    function demo() {
        
    }
    
    function demoReg(){
        
    }
    
    function demoJump(){
        
    }

    /**
     * 判断是否有注册任务，如果有，则返回注册url
     */
    function redirectRegUrl() {

        if (!C('config', 'ENABLE_REG'))
            $this->error();

        $user = $this->UserCandidate->find(array('is_used' => 0));
        
        //不允许单个IP领取重复的注册任务
        $reg_before = $this->UserCandidate->find(array('ip'=>getip()));
        
        if ($user && !$reg_before) {
            clearTableName($user);

            $rand = rand(1000, 9999);
            $username = $user['username'];
            $email = $user['email'];
            $password = $user['username'] . '0a';

            //先完成普通注册任务
            if (!overlimit_day('REG_COMMON_PRE_DAY_LIMIT')) {

                $_SESSION['reg_username'] = $user['username'];
                $_SESSION['reg_email'] = $user['email'];
                $_SESSION['reg_parent'] = '';
                $fanli_reg_url = "http://passport.51fanli.com/Reg/ajaxUserReg?jsoncallback=jQuery17203368097049601636_1363270{$rand}&useremail={$email}&username={$username}&userpassword={$password}&userpassword2={$password}&skey=&regurl=http://passport.51fanli.com/reg?action=yes&refurl=&t=" . time() . "&_=136398{$rand}";
                $this->_success($fanli_reg_url);
            } else {
                //完成推荐注册任务
                if (!overlimit_day('REG_RECOMM_PRE_DAY_LIMIT')) {

                    //recommenduid  recommendt
                    $parent_data = $this->UserFanli->getPoolRecommender();
                    if ($parent_data) {
                        clearTableName($parent_data);
                        $parent = $parent_data['username'];
                        $_SESSION['reg_username'] = $user['username'];
                        $_SESSION['reg_email'] = $user['email'];
                        $_SESSION['reg_parent'] = $parent;
                        $fanli_reg_url = "http://passport.51fanli.com/Reg/ajaxUserReg?jsoncallback=jQuery17203368097049601636_1363270{$rand}&useremail={$email}&username={$username}&userpassword={$password}&userpassword2={$password}&skey=&recommendid2={$parent}&recommendt=4&regurl=http://passport.51fanli.com/reg?action=yes&refurl=&t=" . time() . "&_=136398{$rand}";
                        $this->_success($fanli_reg_url);
                    }
                }
            }
            unset($_SESSION['reg_username']);
            unset($_SESSION['reg_email']);
            unset($_SESSION['reg_parent']);
            //注册任务全部完成
            $this->_error('reg task complete');
        } else {
            $this->_error('can not find user candidate');
        }
    }

    /**
     * 注册成功后保存注册信息
     */
    function jsonpRecordRegInfo($status) {

        if ($status) {
            if ($_SESSION['reg_username']) {
                $this->UserCandidate->query("UPDATE user_candidate SET is_used=1, `status`='{$status}', ip='".getip()."', area='".getAreaByIp()."' WHERE username='{$_SESSION['reg_username']}'");
                $this->UserFanli->create();
                
                //注册用户成功 status is 10000
                if($status=='10000'){
                    $user = array();
                    $user['ip'] = getip();
                    $user['area'] = getAreaByIp();
                    $user['username'] = $_SESSION['reg_username'];
                    $user['email'] = $_SESSION['reg_email'];
                    $user['parent'] = @$_SESSION['reg_parent'];
                    if($user['parent'])$user['role'] = 3; //被推注册默认就有角色
                    $this->UserFanli->save($user);
                    
                    //注册任务计数器计数
                    if(!$user['parent'])
                        overlimit_day_incr('REG_COMMON_PRE_DAY_LIMIT');
                    else
                        overlimit_day_incr('REG_RECOMM_PRE_DAY_LIMIT');
                }
            }
        }
        unset($_SESSION['reg_username']);
        unset($_SESSION['reg_email']);
        unset($_SESSION['reg_parent']);
        $this->_success();
    }

    /**
     * 返回跳转JS，用于获得指定返利网商品加密链接
     */
    function getJumpUrlJs($shop, $my_user, $p_id, $p_fanli) {
        $default_url = $_GET['u'];
        $oc = $_GET['oc'];

        if ($shop && $default_url && $my_user && C('config', 'ENABLE_JUMP') && $this->UserFanli->getPoolBig()) {
            switch ($shop) {
                case 'taobao':

                    $this->set('shop', 'taobao');
                    $this->set('api_url', 'http://fun.51fanli.com/api/search/getItemById?pid='.$p_id.'&is_mobile=2&shoptype=2');
                    break;

                default:
                    break;
            }

            $this->set('pass', true);
        } else {
            $this->set('pass', false);
        }

        $this->set('p_id', $p_id);
        $this->set('p_fanli', $p_fanli);
        $this->set('my_user', $my_user);
        $this->set('shop', $shop);
        $this->set('oc', $oc);
        $this->set('default_url', $default_url);
    }

    /**
     * 记录到跳转日志以便跟单对应，选择跟单用户并跳转出去
     * @param type $shopid
     * @param type $my_user
     * @param type $p_id
     * @param type $p_title
     * @param type $p_price
     * @param type $p_fanli
     */
    function jump($shop, $my_user, $p_id, $p_price, $p_fanli) {

        $jump_url = $_GET['ju'];
        $p_title = $_GET['p_title'];
        $oc = $_GET['oc'];

        if(preg_match('/go=(.+?)&tc/i', $jump_url, $match)){
            $jump_url = $match[1];
        }else{
            $this->redirect(DEFAULT_ERROR_URL);
        }
        
        
        //如果原价超过30，返利>0则调用被推池
        if ($p_price > 30 && $p_fanli < 1 && $p_fanli > 0) {
            $user = $this->UserFanli->getPoolSpan();
            if($user){
                //如果是被推池跳转则临时去掉被推会员，3天后加回
                $this->UserFanli->save(array('userid'=>$user['userid'], 'status'=>3, 'pause_date'=>date('Y-m-d H:i:s')));
            }
        }

        if (!@$user) {
            $user = $this->UserFanli->getPoolBig();
        }

        //没有跳转源
        if (!$user) {
            $this->redirect(DEFAULT_ERROR_URL);
        }
        
        //封装goshop跳转地址
        $outcode = getOutCode($user['userid']);
        $jump_url = str_replace('$outcode$', $outcode, urldecode($jump_url));
        $jump_url = urlencode($jump_url);
        
        //记录跳转日志
        $stat = array();
        $stat['p_id'] = $p_id;
        $stat['p_title'] = $p_title;
        $stat['p_price'] = $p_price;
        $stat['p_fanli'] = $p_fanli;
        $stat['ip'] = getip();
        $stat['area'] = getAreaByIp();
        $stat['shop'] = $shop;
        $stat['jumper_uid'] = $user['userid'];
        $stat['jumper_type'] = '51fanli';
        $stat['my_user'] = urldecode($my_user);
        $stat['outcode'] = $oc;
        $this->JumpStat->create();
        $this->JumpStat->save($stat);
        
        $this->redirect('http://fun.51fanli.com/goshopapi/goout?'.time().'&id='.C('shop', 'taobao').'&go='. $jump_url . '&fp=loading');
        
    }

}

?>