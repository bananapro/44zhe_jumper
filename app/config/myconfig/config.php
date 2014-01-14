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
		'RATE' => 0.38,

		//米折网返利比例，用于跟单还原原始佣金
		'RATE_MIZHE' => 55,
		//给惠网返利比例，用于跟单还原原始佣金
		'RATE_GEIHUI' => 50,
		//宝贝杀返利比例，用于跟单还原原始佣金
		'RATE_BAOBEISHA' => 60,
		//金沙返利比例，用于跟单还原原始佣金
		'RATE_JSFANLI' => 65,
		//返现网返利比例，用于跟单还原原始佣金
		'RATE_FANXIAN' => 80,
		//返利客123返利比例，用于跟单还原原始佣金
		'RATE_FLK123' => 70,
		//淘粉8返利比例，用于跟单还原原始佣金
		'RATE_TAOFEN8' => 65,
		//卷皮网返利比例，用于跟单还原原始佣金
		'RATE_JUANPI' => 70,

		//代理提取订单号
		'PROXY_ORDER' => '452684341951457',

		//默认登陆米折的账户，用于读取跳转链接
		'MIZHE_DEFAULT_LOGIN_USERID' => 5249518,

		//商城跳转单个用户允许最小间隔
		'SHOP_JUMP_DS_TIME' => 3*3600,

		//新绑定跳转渠道分配
		//'JUMP_CHANNEL' => array('geihui'=>1, 'mizhe'=>1, 'baobeisha'=>1, 'jsfanli'=>1, 'flk123'=>1, 'fanxian'=>2, 'taofen8'=>2),
		'JUMP_CHANNEL' => array('baobeisha'=>1, 'jsfanli'=>1, 'fanxian'=>1, 'juanpi'=>1),

		//渠道临时故障转移(网站故障)
		'JUMP_CHANNEL_ENABLE' => array(),

		//有效渠道，设置为无效时，程序自动绑定账号到新渠道
		//'JUMP_CHANNEL_ENABLE' => array('geihui', 'mizhe', 'baobeisha', 'jsfanli', 'flk123', 'fanxian', 'taofen8', 'juanpi'),
		'JUMP_CHANNEL_ENABLE' => array('baobeisha', 'jsfanli', 'fanxian', 'juanpi'),

		//渠道会员绑定插件账号数量上限
		'JUMP_CHANNEL_BIND_LIMIT' => 25,

		//rebates每日跳转限制
		'JUMP_LIMIT_REBATES' => 100,
	)
?>
