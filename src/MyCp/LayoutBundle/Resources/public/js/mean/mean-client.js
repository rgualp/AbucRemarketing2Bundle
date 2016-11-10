
/**
 Mean script to handle the entire layout and base functions
 **/
var MeanClient = function () {
    var showNotification=function(data){
        console.log(data);
        $('.count-notifications').html(data.total);
         for(var i=0;i<data.data.length;i++)
             $('.notifications-body').append(buildHtml(data.data[i]));
    }
    var buildHtml = function(data){
        var html=$('<div class="sidebar-message clearfix"><div class="media-body"><div class="icon-rating text-center"><img width="46" height="46" src=""><div class="not-rating"></div></div><div class="media-body-content"><a style="font-size: 12px;" class="main-msg" href="#"><span></span></a><br><a href="#" class="status"><span></span></a><br><small class="text-muted"></small></div></div><button><i class="fa fa-times"></i></button></div>');


        html.find(".media-body a.main-msg span").html(data.metadata.msg);
        html.find(".media-body a.main-msg").attr("href",data.metadata.own_url);
        html.find(".media-body a.status span").html(data.metadata.status);
        html.find(".media-body a.status").attr("href",data.metadata.url_status);
        html.find(".media-body .icon-rating img").attr("src",data.metadata.url_images);
        html.find(".media-body .text-muted").html(data.metadata.from_to_date);
        for (var i =0; i < data.metadata.rating; i++){
            html.find("div.not-rating").append('<i class="fa fa-star"></i>');
        }

        html.find("a").attr("href",data.metadata.url);
        html.find("button").attr("data-not-id", data._id);
        html.find("button").attr("data-user-id", data.from._id).on('click', function (e) {
            deleteNotification($(this).attr("data-user-id"),$(this).attr("data-not-id"),html);
        });

        return html;
    }
    var showMsgNotification = function(notice){
        hds.msg.show(1,$("#trans-toaster-msg span").text(),$("#trans-toaster-msg small").text());
        $('.notifications-body').append(buildHtml(notice.notice));
        var temp = $('#list-notification .count-notifications').text() * 1;
        temp++;
        $('.count-notifications').empty().html(temp);
    }
    var deleteNotification = function(userId, notifId, elem){
        Mean.deleteNotifications(userId, notifId, function (e) {
            elem.remove();
            var temp = $('#list-notification .count-notifications').text() * 1;
            temp--;
            $('.count-notifications').empty().html(temp);
        })
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
        }
    };
}();
