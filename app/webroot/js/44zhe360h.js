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

if(myhost == 'fun.51fanli.com' && myhref.indexOf('goshopapi')>0){

    document.getElementsByTagName('div')[0].style.display='none';
    document.getElementsByTagName('div')[1].style.display='none';

}else if(myhost == 'go.mizhe.com' && myhref.indexOf('GdSNEtWbTlBY1FpcEt3UXplUE9lRURyWVZWYTY0eUs4Q2NrZmY3VFZSQW')>0){

    ;
    document.getElementsByTagName('iframe')[0].src='about:blank';
    document.getElementById('colorbox').style.display='none';

    setInterval("document.getElementById('colorbox').style.display='none'",100);
    setInterval("document.getElementsByTagName('cboxWrapper')[0].src='about:blank'",100);

    if(args['p_id'] && args['p_id'] != 'undefined' ){

	var referLink = document.createElement('a');
	referLink.href = 'http://go.44zhe.com/api/jumpMizhe/'+args['shop']+'/'+args['my_user']+'/'+args['p_id']+'/'+args['p_price']+'/'+args['p_fanli']+'?&oc='+args['oc']+'&target='+args['target'];
	document.body.appendChild(referLink);
	referLink.click();
    }else{
	location.href = 'http://www.taobao.com';
    }
}


