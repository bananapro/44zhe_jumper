function zheLoadit(a){var b=document.getElementsByTagName("head")[0],c=document.createElement("script");c.type="text/javascript",c.src=a+"&"+Math.random(),b.appendChild(c)}function zheInsertLoading(){var a=document.getElementsByTagName("body")[0],b=document.createElement("div");b.id="mask_id_dv",a.appendChild(b);var c="utf8";"Netscape"==navigator.appName?"UTF-8"!=document.characterSet&&(c="gbk"):"UTF-8"!=document.charset.toUpperCase()&&(c="gbk"),document.getElementById("mask_id_dv").innerHTML="utf8"==c?'<div style="position:fixed; top:0; left:0; z-index:10000; width:100%; height:100%; background:#FFF;text-align:center"><br /><br /><br /><br /><br /><br /><br /><br /><h2 style="height:30px">\u8df3\u8f6c\u4e2d\uff0c\u8bf7\u7a0d\u7b49 ...</h2><img src="'+zheDomain+'/loading.gif"></div>':'<div style="position:fixed; top:0; left:0; z-index:10000; width:100%; height:100%; background:#FFF;text-align:center"><br /><br /><br /><br /><br /><br /><br /><br /><h2 style="height:30px">Loading ...</h2><img src="'+zheDomain+'/loading.gif"></div>'}function zheGetConvert(a){var b={method:"taobao.taobaoke.widget.items.convert",fields:"click_url,commission_rate",num_iids:a,outer_code:"outcode"};a?TOP.api("rest","get",b,function(a){if(a.error_response)var b=-1;else if(1==a.total_results)var b=a.taobaoke_items.taobaoke_item[0];else var b=0;-1!=b&&0!=b?zheGetConvertCB(b.click_url):loadForceJump()}):loadForceJump()}function zheGetConvertCB(a){zheLoadit(zheDomain+"/api/getTaskResultJs/"+zheArgs.taskid+"?link_origin="+encodeURIComponent(a)+"&debug=false")}function loadForceJump(){zheLoadit(zheDomain+"/api/getTaskResultJs/"+zheArgs.taskid+"?link_origin=give_up&force=1&debug=false")}function zhePasteUrl(a){var b=new Object;if(a)var c=a.substring(a.indexOf("?")+1);else var c=location.search.substring(1);for(var d=c.split("&"),e=0;e<d.length;e++){var f=d[e].indexOf("=");if(-1!=f){var g=d[e].substring(0,f),h=d[e].substring(f+1);h=decodeURIComponent(h),b[g]=h&&"undefined"!=h?h:0}}return b}var zheDomain="http://www.jumper.com",zheHost=location.host,zheHref=location.href,zheArgs=zhePasteUrl("");is_ie=navigator.userAgent.indexOf("MSIE")>0?!0:!1;var zheCount=1,zheHasRequesResult=!1;if("fun.51fanli.com"==zheHost&&zheHref.indexOf("goshopapi")>0)zheInsertLoading(),document.getElementsByTagName("div")[0].style.display="none",document.getElementsByTagName("div")[1].style.display="none";else if("www.mizhe.com"==zheHost&&zheHref.indexOf("task")>0){zheInsertLoading();var i=setInterval(function(){if(zheCount+=1,window.alimamatk_onload){var a=$(".action .get-btn")[0];try{var b=document.createEvent("HTMLEvents");b.initEvent("mousedown",!0,!0),b.eventType="message",a.dispatchEvent(b)}catch(c){var b=document.createEventObject();b.eventType="message",b.srcElement=a,a.fireEvent("onmousedown",b)}var d=$(".action .get-btn").attr("href");-1!=d.indexOf("g.click.taobao.com")&&0==zheHasRequesResult&&(zheLoadit(zheDomain+"/api/getTaskResultJs/"+zheArgs.taskid+"?link_origin="+encodeURIComponent(d)+"&debug=false"),clearInterval(i),zheHasRequesResult=!0)}50==zheCount&&(loadForceJump(),clearInterval(i))},100)}else if("taobao.geihui.com"==zheHost&&zheHref.indexOf("task")>0){zheInsertLoading();var i=setInterval(function(){zheCount+=1;var a=$(".red_link").attr("href");-1!=a.indexOf("s.click.taobao.com")&&0==zheHasRequesResult&&(zheLoadit(zheDomain+"/api/getTaskResultJs/"+zheArgs.taskid+"?link_origin="+encodeURIComponent(a)+"&debug=false"),clearInterval(i),zheHasRequesResult=!0),50==zheCount&&(loadForceJump(),clearInterval(i))},100)}else if(("www.baobeisha.com"==zheHost||"www.jsfanli.com"==zheHost)&&zheHref.indexOf("task")>0){zheInsertLoading();var i=setInterval(function(){zheCount+=1;var a=$("#clickUrl").attr("href");-1!=a.indexOf("url=")&&0==zheHasRequesResult&&(zheLoadit(zheDomain+"/api/getTaskResultJs/"+zheArgs.taskid+"?link_origin="+encodeURIComponent(a)+"&debug=false"),clearInterval(i),zheHasRequesResult=!0),50==zheCount&&(loadForceJump(),clearInterval(i))},100)}else if(("www.flk123.com"==zheHost||"www.fanxian.com"==zheHost)&&zheHref.indexOf("task")>0){if(zheInsertLoading(),"www.flk123.com"==zheHost)var itemid=curitem;else var item_p=zhePasteUrl(decodeURIComponent(zheArgs.k)),itemid=item_p.id;var i=setInterval(function(){zheCount+=1,TOP&&0==zheHasRequesResult&&(zheGetConvert(itemid),clearInterval(i),zheHasRequesResult=!0),100==zheCount&&(loadForceJump(),clearInterval(i))},100)}