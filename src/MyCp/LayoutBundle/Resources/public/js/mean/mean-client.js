
/**
 Mean script to handle the entire layout and base functions
 **/
var MeanClient = function () {
    var showNotification=function(data){
        $('#count-notifications').html(data.total);
         $('#count-notifications').text(data.total);
         for(var i=0;i<data.data.length;i++)
             $('#notifications-user').append(buildHtml(data.data[i]));
    }
    var buildHtml = function(data){
        var html='<li>'
            +'<a href="mailbox.html">'
            +'<div>'
            +'<i class="fa fa-envelope fa-fw"></i> ' + data.metadata[0].msg
            +'<span class="pull-right text-muted small"></span>'
            +'</div>'
            +'</a>'
            +'<div style="right: 5px; float: right; margin: -20px;"><a>'
            +'<i data-idnotice="'+data._id+'" class="fa fa-trash"></i>|<i class="fa fa-eye"></i></a>'
            +'</div>'
            +'</li>'
            +'<li class="divider"></li>';
        return html;
    }
    var showMsgNotification = function(notice){
        hds.msg.show(1, notice.notice.metadata[0].msg);
        $('#notifications-user').append(buildHtml(notice.notice));
        var temp=$('#count-notifications').text();
        temp++;
        $('#count-notifications').text(temp);
        $('#count-notifications').html(temp);
    }
    var deleteNotification = function(){
        $('#notifications-user').on('click','.fa-trash',function(){
             var el=$(this);
             var idnotice=el.data('idnotice');
             alert(idnotice);
        });
    }
    return {
        //main function to initiate template pages
        init: function (url_server,ws_url) {
            //IMPORTANT!!!: Do not modify the call order.
            Mean.init({
                url_server:url_server,
                ws_url:ws_url
            });
            var event=Mean.getEvent();
            event.getNotifications.add(showNotification,this);
            event.sendNotification.add(showMsgNotification,this);
            deleteNotification();
        }
    };
}();
