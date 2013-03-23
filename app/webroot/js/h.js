var myhost = location.host;
var myhref = location.href;

if(myhost == 'fun.51fanli.com' && myhref.indexOf('goshopapi')>0){
    
    var b = document.getElementsByTagName('div');
    document.getElementsByTagName('div')[0].style.display='none';
    
}


