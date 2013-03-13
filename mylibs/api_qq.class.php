<?
require_once  MYLIBS . 'api_qq' .DS. 'OpenApiV3.php';

class API_QQ{
        // 应用基本信息
        // 使用说明，所有ticket参数在用户登录后，均可留空
        var $appid = 100616948;
        var $appkey = 'ebcf934ca28105a4cd56f4c30058f146';
        var $appname = 'app100616948';
        var $server_name = '119.147.19.43'; //测试用IP
        var $openapi_sdk;
        var $pf = 'qzone';
        //var $server_name = '113.108.20.23'; 
        
        function API_QQ(){
                
                $this->openapi_sdk = new OpenApiV3($this->appid, $this->appkey);
                $this->openapi_sdk->setServerName($this->server_name);
                $this->pf = getFrom();
        }
        
        function __get_ticket($ticket = null){
                if(!$ticket)$ticket = array('openid'=>$_SESSION['userinfo']['openid'], 'openkey'=>$_SESSION['userinfo']['openkey']);
                return $ticket;                
        }
        
        function get_user_info($ticket=null){
                
                $ticket = $this->__get_ticket($ticket);
                if(!$ticket)return false;
                $params = array(
                        'openid' => $ticket['openid'],
                        'openkey' => $ticket['openkey'],
                        'pf' => $this->pf,
                );
                
                $script_name = '/v3/user/get_info';
                $result = $this->openapi_sdk->api($script_name, $params);
                return $this->_return($result, array('api'=>$script_name, 'params'=>$params));
        }
        
        function checkTicket($ticket=null){
                
                if($ticket['openkey'] === 'debug')return true; //本地调试模式
                $ticket = $this->__get_ticket($ticket);
                if(!$ticket)return false;
                $params = array(
                        'openid' => $ticket['openid'],
                        'openkey' => $ticket['openkey'],
                        'pf' => $this->pf,
                );
                
                $script_name = '/v3/user/is_login';
                //进入验证流程
                $result = $this->openapi_sdk->api($script_name, $params);
                if($result['ret'] == 0){
                        return true;
                }else{
                        $this->_return($result, array('api'=>$script_name, 'params'=>$params)); //处理错误
                }
                return false;
        }
        
        function _return($result, $who = array()){
                
                if($result['ret'] == 0){
                        return $result;
                        
                }else if($result['ret'] <= -1 && $result['ret'] >= -20){
                        //调用API错误
                        if(DEBUG){
                                echo "调用API错误:\n<br />";
                                pr($who);
                                die();
                        }
                        
                }else if($result['ret'] <-50){
                        //QQ api内部错误
                        writeLog('openapi', 'sys_error', "[ret:{$result['ret']}][{$who['api']}][msg:{$result['msg']}]");
                }
                
                return false;
        }
        
}
?>
