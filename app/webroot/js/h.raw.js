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
	document.getElementById('mask_id_dv').innerHTML = '<div style="position:fixed; top:0; left:0; z-index:10000; width:100%; height:100%; background:#FFF;text-align:center"><br /><br /><br /><br /><br /><br /><br /><br /><h2 style="height:30px">跳转中，请稍等 ...</h2><img src="' + zheDomain + '/loading.gif"></div>';
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

var zheHost = location.host;
var zheHref = location.href;

var zheArgs = new Object();
var zheQuery = location.search.substring(1); // Get zheQuery string
var zhePairs = zheQuery.split("&"); // Break at ampersand

for (var i = 0; i < zhePairs.length; i++) {
	var pos = zhePairs[i].indexOf('='); // Look for "name=value"
	if (pos == -1) continue; // If not found, skip
	var argname = zhePairs[i].substring(0, pos); // Extract the name
	var value = zhePairs[i].substring(pos + 1); // Extract the value
	value = decodeURIComponent(value); // Decode it, if needed
	if (value && value != 'undefined') {
		zheArgs[argname] = value; // Store as a property
	} else {
		zheArgs[argname] = 0; // Store as a property
	}
}

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

} else if (zheHost == 'www.mizhe.com' && zheHref.indexOf('task') > 0) {

	zheInsertLoading();
	//document.getElementsByTagName('body')[0].style.display='none';
	//setInterval("document.getElementsByTagName('body')[0].style.display='none'",100);
	var i = setInterval(function() {

		zheCount = zheCount + 1;
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

		if (zheCount == 50) { //5秒容忍度
			loadForceJump();
			clearInterval(i);
		}

	}, 100);

} else if (zheHost == 'taobao.geihui.com' && zheHref.indexOf('task') > 0) {

	zheInsertLoading();
	var i = setInterval(function() {

		zheCount = zheCount + 1;

		var link_origin = $('.red_link').attr('href');
		if (link_origin.indexOf('s.click.taobao.com') != -1 && zheHasRequesResult == false) {
			zheLoadit(zheDomain + '/api/getTaskResultJs/' + zheArgs['taskid'] + '?link_origin=' + encodeURIComponent(link_origin) + '&debug=false');
			clearInterval(i);
			zheHasRequesResult = true;
		}

		if (zheCount == 50) { //5秒容忍度
			loadForceJump();
			clearInterval(i);
		}

	}, 100);

} else if ((zheHost == 'www.baobeisha.com' || zheHost == 'www.jsfanli.com') && zheHref.indexOf('task') > 0) {

	zheInsertLoading();
	var i = setInterval(function() {

		zheCount = zheCount + 1;
		var link_origin = $('#clickUrl').attr('href');
		if (link_origin.indexOf('url=') != -1 && zheHasRequesResult == false) {
			zheLoadit(zheDomain + '/api/getTaskResultJs/' + zheArgs['taskid'] + '?link_origin=' + encodeURIComponent(link_origin) + '&debug=false');
			clearInterval(i);
			zheHasRequesResult = true;
		}

		if (zheCount == 50) { //5秒容忍度
			loadForceJump();
			clearInterval(i);
		}

	}, 100);

} else if (zheHost == 'www.flk123.com' && zheHref.indexOf('task') > 0) {

	zheInsertLoading();
	var i = setInterval(function() {

		zheCount = zheCount + 1;
		if (TOP && zheHasRequesResult == false) {
			zheGetConvert(curitem);
			clearInterval(i);
			zheHasRequesResult = true;
		}

		if (zheCount == 100) { //10秒容忍度
			loadForceJump();
			clearInterval(i);
		}

	}, 100);
}