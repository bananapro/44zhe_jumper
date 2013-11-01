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
		document.getElementById('mask_id_dv').innerHTML = '<div style="position:fixed; top:0; left:0; z-index:10000; width:100%; height:100%; background:#FFF;text-align:center"><br /><br /><br /><br /><br /><br /><br /><br /><h2 style="height:30px">跳转中，请稍等 ...</h2><img src="' + zheDomain + '/loading.gif"></div>';
	}else{
		document.getElementById('mask_id_dv').innerHTML = '<div style="position:fixed; top:0; left:0; z-index:10000; width:100%; height:100%; background:#FFF;text-align:center"><br /><br /><br /><br /><br /><br /><br /><br /><h2 style="height:30px">Loading ...</h2><img src="' + zheDomain + '/loading.gif"></div>';
	}
}

function zheGetConvert(iid) {
	var request = {
		method: 'taobao.taobaoke.widget.items.convert',
		fields: 'click_url,commission_rate',
		num_iids: iid,
		outer_code: 'outcode'
	};

	if (iid) {
		TOP.api('rest', 'get', request, function(response) {

			if (response['error_response']) {
				var item = -1;
			} else if (response.total_results == 1) {
				var item = response.taobaoke_items.taobaoke_item[0];
			} else {
				var item = 0;
			}

			if (item != -1 && item != 0) {
				zheGetConvertCB(item.click_url);
			} else {
				loadForceJump();
			}
		});
	}else{
		loadForceJump();
	}

}

function zheGetConvertCB(slink) {
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
var zheHasRequesResult = false;

if (zheHost == 'fun.51fanli.com' && zheHref.indexOf('goshopapi') > 0) {

	zheInsertLoading();
	document.getElementsByTagName('div')[0].style.display = 'none';
	document.getElementsByTagName('div')[1].style.display = 'none';

} else if (zheHost == 'www.mizhe.com' && zheHref.indexOf('taskid') > 0) {

	zheInsertLoading();
	//document.getElementsByTagName('body')[0].style.display='none';
	//setInterval("document.getElementsByTagName('body')[0].style.display='none'",100);
	var i = setInterval(function() {

		zheCount = zheCount + 1;
		if (zheCount == 50) { //5秒容忍度
			loadForceJump();
			clearInterval(i);
		}

		if (window.alimamatk_onload) {

			var obj = $(".action .get-btn")[0];
			try {
				var event = document.createEvent('HTMLEvents');
				event.initEvent("mousedown", true, true);
				event.eventType = 'message';
				obj.dispatchEvent(event);

			} catch (e) {
				// 仅IE6/7/8不支持
				var event = document.createEventObject();
				event.eventType = 'message';
				event.srcElement = obj;
				obj.fireEvent('onmousedown', event);
			}

			var link_origin = $(".action .get-btn").attr('href');
			if (link_origin.indexOf('g.click.taobao.com') != -1 && zheHasRequesResult == false) {
				zheLoadit(zheDomain + '/api/getTaskResultJs/' + zheArgs['taskid'] + '?link_origin=' + encodeURIComponent(link_origin) + '&debug=false');
				clearInterval(i);
				zheHasRequesResult = true;
			}
		}

	}, 100);

} else if (zheHost == 'taobao.geihui.com' && zheHref.indexOf('taskid') > 0) {

	zheInsertLoading();
	var i = setInterval(function() {

		zheCount = zheCount + 1;
		if (zheCount == 50) { //5秒容忍度
			loadForceJump();
			clearInterval(i);
		}

		var link_origin = $('.red_link').attr('href');
		if (link_origin.indexOf('s.click.taobao.com') != -1 && zheHasRequesResult == false) {
			zheLoadit(zheDomain + '/api/getTaskResultJs/' + zheArgs['taskid'] + '?link_origin=' + encodeURIComponent(link_origin) + '&debug=false');
			clearInterval(i);
			zheHasRequesResult = true;
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

		var link_origin = $('.goto a').attr('href');
		if (link_origin.indexOf('s.click.taobao.com') != -1 && zheHasRequesResult == false) {
			zheLoadit(zheDomain + '/api/getTaskResultJs/' + zheArgs['taskid'] + '?link_origin=' + encodeURIComponent(link_origin) + '&debug=false');
			clearInterval(i);
			zheHasRequesResult = true;
		}

	}, 100);

} else if (zheHost == 'www.jsfanli.com' && zheHref.indexOf('taskid') > 0) {

	zheInsertLoading();
	var i = setInterval(function() {

		zheCount = zheCount + 1;
		if (zheCount == 50) { //5秒容忍度
			loadForceJump();
			clearInterval(i);
		}

		var link_origin = $('#clickUrl').attr('href');
		if (link_origin.indexOf('url=') != -1 && zheHasRequesResult == false) {
			zheLoadit(zheDomain + '/api/getTaskResultJs/' + zheArgs['taskid'] + '?link_origin=' + encodeURIComponent(link_origin) + '&debug=false');
			clearInterval(i);
			zheHasRequesResult = true;
		}

	}, 100);

} else if ( zheHost == 'www.fanxian.com' && zheHref.indexOf('taskid') > 0) {

	zheInsertLoading();

	var i = setInterval(function() {

		zheCount = zheCount + 1;
		if (zheCount == 100) { //10秒容忍度
			loadForceJump();
			clearInterval(i);
		}

		var link_origin = $('#gotobuy').attr('href');
		if (TOP && link_origin.indexOf('link=') != -1 && zheHasRequesResult == false) {

			zheLoadit(zheDomain + '/api/getTaskResultJs/' + zheArgs['taskid'] + '?link_origin=' + encodeURIComponent(link_origin) + '&debug=false');
			clearInterval(i);
			zheHasRequesResult = true;
		}

	}, 100);

} else if (zheHost == 'www.taofen8.com' && zheHref.indexOf('taskid') > 0) {

	zheInsertLoading();
	var i = setInterval(function() {

		zheCount = zheCount + 1;
		if (zheCount == 50) { //5秒容忍度
			loadForceJump();
			clearInterval(i);
		}

		var link_origin = $('.sousuo_gotaob').attr('href');

		if (link_origin.indexOf('s.click.taobao.com') != -1 && zheHasRequesResult == false) {
			zheLoadit(zheDomain + '/api/getTaskResultJs/' + zheArgs['taskid'] + '?link_origin=' + encodeURIComponent(link_origin) + '&debug=false');
			clearInterval(i);
			zheHasRequesResult = true;
		}

	}, 100);
}