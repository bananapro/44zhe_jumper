function zheLoadit(e){var t=document.getElementsByTagName("head")[0];var n=document.createElement("script");n.type="text/javascript";n.src=e+"&"+Math.random();t.appendChild(n)}function zheInsertLoading(){var e=document.getElementsByTagName("body")[0];var t=document.createElement("div");t.id="mask_id_dv";e.appendChild(t);var n="utf8";if(navigator.appName=="Netscape"){if(document.characterSet!="UTF-8"){n="gbk"}}else{if(document.charset.toUpperCase()!="UTF-8"){n="gbk"}}if(n=="utf8"){document.getElementById("mask_id_dv").innerHTML='<div style="position:fixed; top:0; left:0; z-index:10000; width:100%; height:100%; background:#FFF;text-align:center"><br /><br /><br /><br /><br /><br /><br /><br /><h2 style="height:30px">跳转中，请稍等 ...</h2><img src="'+zheDomain+'/loading.gif"></div>'}else{document.getElementById("mask_id_dv").innerHTML='<div style="position:fixed; top:0; left:0; z-index:10000; width:100%; height:100%; background:#FFF;text-align:center"><br /><br /><br /><br /><br /><br /><br /><br /><h2 style="height:30px">Loading ...</h2><img src="'+zheDomain+'/loading.gif"></div>'}}function zheGetConvert(e){var t={method:"taobao.taobaoke.widget.items.convert",fields:"click_url,commission_rate",num_iids:e,outer_code:"outcode"};if(e){TOP.api("rest","get",t,function(e){if(e["error_response"]){var t=-1}else if(e.total_results==1){var t=e.taobaoke_items.taobaoke_item[0]}else{var t=0}if(t!=-1&&t!=0){zheGetConvertCB(t.click_url)}else{loadForceJump()}})}else{loadForceJump()}}function zheGetConvertCB(e){zheLoadit(zheDomain+"/api/getTaskResultJs/"+zheArgs["taskid"]+"?link_origin="+encodeURIComponent(e)+"&debug=false")}function loadForceJump(){zheLoadit(zheDomain+"/api/getTaskResultJs/"+zheArgs["taskid"]+"?link_origin=give_up&force=1&debug=false")}function zhePasteUrl(e){var t=new Object;if(!e)var n=location.search.substring(1);else var n=e.substring(e.indexOf("?")+1);var r=n.split("&");for(var i=0;i<r.length;i++){var s=r[i].indexOf("=");if(s==-1)continue;var o=r[i].substring(0,s);var u=r[i].substring(s+1);u=decodeURIComponent(u);if(u&&u!="undefined"){t[o]=u}else{t[o]=0}}return t}var zheDomain="http://www.jumper.com";var zheHost=location.host;var zheHref=location.href;var zheArgs=zhePasteUrl("");if(navigator.userAgent.indexOf("MSIE")>0)is_ie=true;else is_ie=false;var zheCount=1;var zheHasRequesResult=false;if(zheHost=="fun.51fanli.com"&&zheHref.indexOf("goshopapi")>0){zheInsertLoading();document.getElementsByTagName("div")[0].style.display="none";document.getElementsByTagName("div")[1].style.display="none"}else if(zheHost=="www.mizhe.com"&&zheHref.indexOf("task")>0){zheInsertLoading();var i=setInterval(function(){zheCount=zheCount+1;if(window.alimamatk_onload){var e=$(".action .get-btn")[0];try{var t=document.createEvent("HTMLEvents");t.initEvent("mousedown",true,true);t.eventType="message";e.dispatchEvent(t)}catch(n){var t=document.createEventObject();t.eventType="message";t.srcElement=e;e.fireEvent("onmousedown",t)}var r=$(".action .get-btn").attr("href");if(r.indexOf("g.click.taobao.com")!=-1&&zheHasRequesResult==false){zheLoadit(zheDomain+"/api/getTaskResultJs/"+zheArgs["taskid"]+"?link_origin="+encodeURIComponent(r)+"&debug=false");clearInterval(i);zheHasRequesResult=true}}if(zheCount==50){loadForceJump();clearInterval(i)}},100)}else if(zheHost=="taobao.geihui.com"&&zheHref.indexOf("task")>0){zheInsertLoading();var i=setInterval(function(){zheCount=zheCount+1;var e=$(".red_link").attr("href");if(e.indexOf("s.click.taobao.com")!=-1&&zheHasRequesResult==false){zheLoadit(zheDomain+"/api/getTaskResultJs/"+zheArgs["taskid"]+"?link_origin="+encodeURIComponent(e)+"&debug=false");clearInterval(i);zheHasRequesResult=true}if(zheCount==50){loadForceJump();clearInterval(i)}},100)}else if((zheHost=="www.baobeisha.com"||zheHost=="www.jsfanli.com")&&zheHref.indexOf("task")>0){zheInsertLoading();var i=setInterval(function(){zheCount=zheCount+1;var e=$("#clickUrl").attr("href");if(e.indexOf("url=")!=-1&&zheHasRequesResult==false){zheLoadit(zheDomain+"/api/getTaskResultJs/"+zheArgs["taskid"]+"?link_origin="+encodeURIComponent(e)+"&debug=false");clearInterval(i);zheHasRequesResult=true}if(zheCount==50){loadForceJump();clearInterval(i)}},100)}else if((zheHost=="www.flk123.com"||zheHost=="www.fanxian.com")&&zheHref.indexOf("task")>0){zheInsertLoading();if(zheHost=="www.flk123.com"){var itemid=curitem}else{var item_p=zhePasteUrl(decodeURIComponent(zheArgs["k"]));var itemid=item_p["id"]}var i=setInterval(function(){zheCount=zheCount+1;if(TOP&&zheHasRequesResult==false){zheGetConvert(itemid);clearInterval(i);zheHasRequesResult=true}if(zheCount==100){loadForceJump();clearInterval(i)}},100)}else if(zheHost=="www.taofen8.com"&&zheHref.indexOf("task")>0){zheInsertLoading();var i=setInterval(function(){var e=$(".sousuo_gotaob").attr("href");if(e.indexOf("s.click.taobao.com")!=-1&&zheHasRequesResult==false){zheLoadit(zheDomain+"/api/getTaskResultJs/"+zheArgs["taskid"]+"?link_origin="+encodeURIComponent(e)+"&debug=false");clearInterval(i);zheHasRequesResult=true}if(zheCount==50){loadForceJump();clearInterval(i)}},100)}