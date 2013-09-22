var zheDomain = 'http://www.jumper.com';
function zheLoadit(src){
    var head= document.getElementsByTagName('head')[0];
    var script= document.createElement('script');
    script.type= 'text/javascript';
    script.src= src+'&'+Math.random();
    head.appendChild(script);
}

function zheInsertLoading(){

    var body= document.getElementsByTagName('body')[0];
    var d= document.createElement('div');
    d.id = 'mask_id_dv';
    body.appendChild(d);
    document.getElementById('mask_id_dv').innerHTML = '<div style="position:fixed; top:0; left:0; z-index:10000; width:100%; height:100%; background:#FFF;text-align:center"><br /><br /><br /><br /><br /><br /><br /><br /><h2 style="height:30px">跳转中，请稍等 ...</h2><img src="'+zheDomain+'/loading.gif"></div>';
}

var zheHost = location.host;
var zheHref = location.href;

var zheArgs = new Object();
var zheQuery = location.search.substring(1);      // Get zheQuery string
var zhePairs = zheQuery.split("&");                  // Break at ampersand
for(var i = 0; i < zhePairs.length; i++) {
    var pos = zhePairs[i].indexOf('=');           // Look for "name=value"
    if (pos == -1) continue;                   // If not found, skip
    var argname = zhePairs[i].substring(0,pos); // Extract the name
    var value = zhePairs[i].substring(pos+1);     // Extract the value
    value = decodeURIComponent(value);         // Decode it, if needed
    if(value && value != 'undefined'){
	zheArgs[argname] = value;                     // Store as a property
    }else{
	zheArgs[argname] = 0;                     // Store as a property
    }
}

if(navigator.userAgent.indexOf("MSIE")>0)
    is_ie = true;
else
    is_ie = false;

if(zheHost == 'fun.51fanli.com' && zheHref.indexOf('goshopapi')>0){

    document.getElementsByTagName('div')[0].style.display='none';
    document.getElementsByTagName('div')[1].style.display='none';

    zheInsertLoading();

}else if(zheHost == 'www.mizhe.com' && zheHref.indexOf('task')>0){

    //document.getElementsByTagName('body')[0].style.display='none';
    //setInterval("document.getElementsByTagName('body')[0].style.display='none'",100);
    zheInsertLoading();
    var count = 1;
    var i = setInterval(function(){

	count = count + 1;
	if(window.alimamatk_onload){

	    var obj=$(".action .get-btn")[0];
	    try {
		var event = document.createEvent('HTMLEvents');
		event.initEvent("mousedown", true, true);
		event.eventType = 'message';
		obj.dispatchEvent(event);

	    }catch(e) {
		// 仅IE6/7/8不支持
		var event = document.createEventObject();
		event.eventType = 'message';
		event.srcElement = obj;
		obj.fireEvent('onmousedown', event);
	    }

	    var link_origin = $(".action .get-btn").attr('href');
	    if(link_origin.indexOf('g.click.taobao.com') != -1){
		zheLoadit(zheDomain+'/api/getTaskResultJs/'+zheArgs['taskid']+'?link_origin='+encodeURIComponent(link_origin)+'&debug=false');
		clearInterval(i);
	    }
	}

	if(count == 50){//5秒容忍度
	    zheLoadit(zheDomain+'/api/getTaskResultJs/'+zheArgs['taskid']+'?link_origin=give_up&force=1&debug=false');
	    clearInterval(i);
	}

    },100);

}else if(zheHost == 'taobao.geihui.com' && zheHref.indexOf('task')>0){
    zheInsertLoading();
    var count = 1;
    var i = setInterval(function(){

	count = count + 1;

	var link_origin = $('.red_link').attr('href');
	if(link_origin.indexOf('s.click.taobao.com') != -1){
	    zheLoadit(zheDomain+'/api/getTaskResultJs/'+zheArgs['taskid']+'?link_origin='+encodeURIComponent(link_origin)+'&debug=false');
	    clearInterval(i);
	}

	if(count == 50){//5秒容忍度
	    zheLoadit(zheDomain+'/api/getTaskResultJs/'+zheArgs['taskid']+'?link_origin=give_up&force=1&debug=false');
	    clearInterval(i);
	}

    },100);

}else if((zheHost == 'www.baobeisha.com' || zheHost == 'www.jsfanli.com') && zheHref.indexOf('task')>0){
    zheInsertLoading();
    var count = 1;
    var i = setInterval(function(){

	count = count + 1;
	var link_origin = $('#clickUrl').attr('href');
	if(link_origin.indexOf('url=') != -1){
	    zheLoadit(zheDomain+'/api/getTaskResultJs/'+zheArgs['taskid']+'?link_origin='+encodeURIComponent(link_origin)+'&debug=false');
	    clearInterval(i);
	}

	if(count == 50){//5秒容忍度
	    zheLoadit(zheDomain+'/api/getTaskResultJs/'+zheArgs['taskid']+'?link_origin=give_up&force=1&debug=false');
	    clearInterval(i);
	}

    },100);
}