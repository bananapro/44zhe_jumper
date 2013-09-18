function doRegProxy(vobj){

    $.ajax({
        dataType: "jsonp",
        url: "http://www.jumper.com/api/redirectRegUrl?debug=false",
        jsonp:"jsoncallback",
        success: function(e){

            if(e.status == 1){

                $.ajax({
                    dataType:"jsonp",
                    jsonp:"jsoncallback",
                    url:e.message,

                    //请求第三方注册url
                    //无论正确与否都进行记录
                    success:function(e){
                        $.ajax({
                            dataType:"jsonp",
                            jsonp:"jsoncallback",
                            url:"http://www.jumper.com/api/jsonpRecordRegInfo/"+e.status,
                            success:function(){
                                eval(vobj + '(true)');
                            },
                            error:function(){
                                eval(vobj + '(false)');
                            }
                        });
                    },

                    error:function(){
                        //alert('request the reg url error!');
                        eval(vobj + '(false)');
                    }
                });

            }else{
                //alert('sys forbiden to reg');
                eval(vobj + '(false)');
            }
        },

        error: function(){
            //alert('request to get redirectRegUrl error');
            eval(vobj + '(false)');
        }
    });
}
