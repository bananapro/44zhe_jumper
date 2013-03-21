function doRegProxy(){
    
    $.ajax({
        dataType: "jsonp",
        url: "http://go.44zhe.com/api/redirectRegUrl?debug=false",
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
                            url:"http://go.44zhe.com/api/jsonpRecordRegInfo/"+e.status, 
                            success:function(){
                                mycallback();
                            }, 
                            error:function(){
                                mycallback();
                            }
                        });
                    },
                        
                    error:function(){
                        //alert('request the reg url error!');
                        mycallback();
                    }
                });
                    
            }else{
                //alert('sys forbiden to reg');
                mycallback();
            }
        },
        
        error: function(){
            //alert('request to get redirectRegUrl error');
            mycallback();
        }
    });
}