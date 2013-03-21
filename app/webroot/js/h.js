window.onload = function(){
    var ls = location.host;
    if(ls.indexOf('51fanli.com')>0){
        var b = document.getElementsByTagName('body');
        for(i in b){
            b[i].style.display = 'none';
        }
    }
}
