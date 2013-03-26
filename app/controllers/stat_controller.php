<?php

class StatController extends AppController {

    var $name = 'Stat';
    var $uses = array('UserFanli', 'StatJump', 'UserCandidate');


    function basic(){
        //当前配置状态
        $config = C('config');
        $this->set('c', $config);
        
        //昨日、今日关键数据
        $s = array();
        $yesterday = date('Y-m-d', time() - 24*3600);
        $today = date('Y-m-d');
        $s['y_reg_num'] = $this->UserFanli->findCount("created>'".$yesterday."' AND created<'".$today."'");
        $s['t_reg_num'] = $this->UserFanli->findCount("created>'".$today."'");
        
        $s['y_jump_num'] = $this->StatJump->findCount("ts>'".$yesterday."' AND ts<'".$today."'");
        $s['t_jump_num'] = $this->StatJump->findCount("ts>'".$today."'");
        
        $s['y_price_num'] = $this->StatJump->findSum('p_price', "ts>'".$yesterday."' AND ts<'".$today."'");
        $s['t_price_num'] = $this->StatJump->findSum('p_price', "ts>'".$today."'");
        
        $s['y_fanli_num'] = $this->StatJump->findSum('p_fanli', "ts>'".$yesterday."' AND ts<'".$today."'");
        $s['t_fanli_num'] = $this->StatJump->findSum('p_fanli', "ts>'".$today."'");
        
        $this->set('s', $s);
        
        //跳转中介统计
        $j1 = $this->StatJump->query("SELECT count(*) as nu, jumper_uid, username, a.area, sum(p_price) price, sum(p_fanli) fanli FROM stat_jump a LEFT JOIN user_fanli ON(jumper_uid = userid) WHERE a.ts>'".$yesterday."' AND a.ts<'".$today."' GROUP BY jumper_uid ORDER BY nu DESC");
        $j2 = $this->StatJump->query("SELECT count(*) as nu, jumper_uid, username, a.area, sum(p_price) price, sum(p_fanli) fanli FROM stat_jump a LEFT JOIN user_fanli ON(jumper_uid = userid) WHERE a.ts>'".$today."'  GROUP BY jumper_uid ORDER BY nu DESC");
        clearTableName($j1);
        clearTableName($j2);
        $this->set('js1', $j1);
        $this->set('js2', $j2);
        
        
        //最新跳转记录
        $last_jumps = $this->StatJump->findAll('', '', 'id DESC', 10);
        clearTableName($last_jumps);
        $this->set('last_jumps', $last_jumps);
    }

}

?>