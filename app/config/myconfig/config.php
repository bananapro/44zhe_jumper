<?php

    return array(
        
        //注册系统运行
        'ENABLE_REG' => true,
        //跳转系统运行
        'ENABLE_JUMP' => true,

        
        //每日普通注册数限制(优先完成普通任务)
        'REG_COMMON_PRE_DAY_LIMIT' => 5,
        //每日推荐注册数限制
        'REG_RECOMM_PRE_DAY_LIMIT' => 60,
        //限制注册地
        'REG_EXCLUDE_AREA' => array(),
        
        //特殊账号
        'SP_UID' => 5730909,
        'SP_FANLI_MAX' => 600,
        
        //预留被推，以免人工全部都完成
        'LEFT_RECOMMENDER' => 50,
        
        //每月1号大额池筛选固定人数
        'POOL_BIG_PRE_MONTH' => 50, //总固定人数控制100
        
        //每月1号推手池筛选固定人数
        'POOL_RECOMMENDER_PRE_MONTH' => 50, //总固定人数控制100
        
        //每月5号废弃一定量的推手并补充新推手
        'POOL_RECOMMENDER_REMOVE' => 5,
    )
?>
