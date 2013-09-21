<?php

    return array(

        //注册系统运行
        'ENABLE_REG' => true,
        //跳转系统运行
        'ENABLE_JUMP' => true,


        //每日普通注册数限制(优先完成普通任务)
        'REG_COMMON_PRE_DAY_LIMIT' => 3,
        //每日推荐注册数限制
        'REG_RECOMM_PRE_DAY_LIMIT' => 0,
        //限制注册地
        'REG_EXCLUDE_AREA' => array(),

        //特殊账号每月领取额度
        'SP_UID' => 5730909,
        'SP_FANLI_MAX' => 0,

        //预留被推，以免人工全部都完成
        'LEFT_RECOMMENDER' => 50,

        //每月1号大额池筛选固定人数
        'POOL_BIG_PRE_MONTH' => 50, //总固定人数控制100

        //每月1号推手池筛选固定人数
        'POOL_RECOMMENDER_PRE_MONTH' => 50, //总固定人数控制100

        //每月5号废弃一定量的推手并补充新推手
        'POOL_RECOMMENDER_REMOVE' => 5,

		//内部卖家，不做跟单
		'HOLD_SELLER' => array('bluecone'),

		//向阿雄结算费率
		'RATE' => 0.45,

		//米折网返利折扣，用于跟单还原原始佣金
		'RATE_MIZHE' => 55,

		//代理提取订单号
		'PROXY_ORDER' => '382924045081457',

		//默认登陆米折的账户，用于读取跳转链接
		'MIZHE_DEFAULT_LOGIN_USERID' => 5249518,

		//商城跳转单个用户允许最小间隔
		'SHOP_JUMP_DS_TIME' => 3*3600,

		//新绑定跳转渠道分配
		//'JUMP_CHANNEL' => array('taofen8'=>2, 'geihui'=>1, 'bbbao'=>1, 'baobeisha'=>1, 'mizhe'=>1),
		'JUMP_CHANNEL' => array('geihui'=>2, 'mizhe'=>1, 'baobeisha'=>1),

		//渠道临时故障转移(网站故障)
		'JUMP_CHANNEL_ENABLE' => array(),

		//有效渠道，设置为无效时，程序自动绑定账号到新渠道
		//'JUMP_CHANNEL_ENABLE' => array('taofen8', 'geihui', 'bbbao', 'baobeisha', 'mizhe'),
		'JUMP_CHANNEL_ENABLE' => array('geihui', 'mizhe', 'baobeisha'),

		//渠道会员绑定插件账号数量上限
		'JUMP_CHANNEL_BIND_LIMIT' => 20,
    )
?>
