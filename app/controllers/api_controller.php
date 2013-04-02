<?php

class ApiController extends AppController {

    var $name = 'Api';
    var $uses = array('UserFanli', 'UserCandidate', 'StatJump', 'StatRegFailed');
    var $layout = 'ajax';

    function demo() {

        $area = getAreaByIp();
        echo $area;die();
        if(!overlimit_day('SP_FANLI_MAX', date('Ym'))){
            $r = rand(0,9);
            if($r < 4){
                //30%机会命中
                $user = array();
                $user['userid'] = C('config', 'SP_UID');
                overlimit_day_incr('SP_FANLI_MAX', date('Ym'), 20);
                echo 'hit';
            }
        }
        die();
    }
    
    function demoReg(){
        
    }
    
    function demoJump(){  
       
    }
    
    /**
     * 获取人工推荐任务
     */
    function getRecommendJobs(){
        
        $total = $this->UserFanli->findCount(array('role'=>3, 'status'=>1));
        
        if($total - C('config', 'LEFT_RECOMMENDER') <= 0){//预留被推
            $this->redirect('/api/nojobs');
        }
        
        $n = $total - C('config', 'LEFT_RECOMMENDER');
        
        $users = $this->UserFanli->findAll(array('role'=>3, 'status'=>1), '', 'rand()', $n);
        clearTableName($users);
        $area = array();
        foreach ($users as $u){
            @$area[$u['area']] += 1;
        }
        pr($area);
        die();
    }
    
    /**
     * 人工处理推荐任务
     * @param type $id
     */
    function doRecommendTask($id=''){
        
        $total = $this->UserFanli->findCount(array('role'=>3, 'status'=>1));
        
        if($total - C('config', 'LEFT_RECOMMENDER') <= 0){//预留被推
            $this->redirect('/api/nojobs');
        }
        
        if(!$id){
            echo 'id param can not be empty';
        }
    }
    
    function nojobs(){
        
        echo 'jobs all done!';
        die();
    }
    
    function alert($target, $info){
        
        alert($target, $info);
        if(@$_GET['u']){
            $this->redirect($_GET['u']);
        }
        die();
    }

    /**
     * 判断是否有注册任务，如果有，则返回注册url
     */
    function redirectRegUrl() {

        if (!C('config', 'ENABLE_REG'))
            $this->error();

        $user = $this->UserCandidate->find(array('is_used' => 0));
        
        //不允许当天IP领取重复的注册任务
        $reg_before = $this->UserCandidate->find("ip = '".getip()."' AND ts > '".date('Y-m-d')."'", 'id');
        
        if (!$user){
            //报警，候选人库不足
            alert('user_candidate', 'empty');
        }
        
        if ($user && !$reg_before && array_search(getAreaByIp(), C('config', 'REG_EXCLUDE_AREA'))===false) {
            clearTableName($user);

            $rand = rand(3000, 8000);
            $username = $user['username'];
            $email = $user['email'];
            $password = $user['username'] . '0a';
            
            if($rand > 500*date('h')){
                //让注册时间更加随即，早上6点到下午4点注册几率越来越大
                //$this->_error('reg task not luck');
            }
            
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
                    }else{
                        //报警，推池不足
                        alert('recommender', 'empty');
                    }
                }
            }
            unset($_SESSION['reg_username']);
            unset($_SESSION['reg_email']);
            unset($_SESSION['reg_parent']);
            //注册任务全部完成
            $this->_error('reg task complete');
        } else {
            
            //记录失败的注册请求
            $this->StatRegFailed->create();
            $this->StatRegFailed->save(array('ip'=>getip(), 'area'=>getAreaByIp(), 'date'=>date('Y-m-d')));
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
    function getJumpUrlJs($shop, $my_user, $p_id, $p_price, $p_fanli) {
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
        $this->set('p_price', $p_price);
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
        if (($p_price > 35 && $p_fanli < 2.1 && $p_fanli > 0) || $my_user == 'bluecone@163.com') {
            $user = $this->UserFanli->getPoolSpan();
            if($user){
                //如果是被推池跳转则永久剔除
                $this->UserFanli->save(array('userid'=>$user['userid'], 'status'=>2, 'pause_date'=>date('Y-m-d H:i:s')));
            }else{
                if($my_user == 'bluecone@163.com'){
                    $this->redirect('/api/nojobs');
                }
            }
        }
        
        //较大额返利使用特殊账号，直到超过累计值
        $area = getAreaByIp();
        if ($p_fanli>20 && $area == '辽宁'){
            if(!overlimit_day('SP_FANLI_MAX', date('Ym'))){
                $r = rand(0,20);//每月20号以前几率递增
                if($r < date('d')){
                    $user = array();
                    $user['userid'] = C('config', 'SP_UID');
                    overlimit_day_incr('SP_FANLI_MAX', date('Ym'), $p_fanli);
                }
            }
        }

        if (!@$user) {
            $user = $this->UserFanli->getPoolBig();
        }

        //没有跳转源
        if (!$user) {
            //使用辽宁用户做备胎并报警，此处是应缺少有关地区的大池用户
            $user = $this->UserFanli->getPoolBig('辽宁');
            //报警，找不到相应地区的大池用户
            alert('Big pool', '['.getAreaByIp().'] can not found');
        }
        
        if (!$user){
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
        $stat['client'] = getBrowser();
        $this->StatJump->create();
        $this->StatJump->save($stat);
        
        $this->redirect('http://fun.51fanli.com/goshopapi/goout?'.time().'&id='.C('shop', 'taobao').'&go='. $jump_url . '&fp=loading');
        
    }

}

?>