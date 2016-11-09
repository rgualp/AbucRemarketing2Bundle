/**
 Mean script to handle the entire layout and base functions
 **/
var Mean = function () {
  var url_server='';
  var ws_url='';
  var Signal = signals.Signal;
  var event='';
  /**
   * Funcion para inicializar el servidor
   */
  var initializeServerComunication=function(){
    var socket = io.connect(ws_url+"/mean/",{
      'query': 'token=' + access_token,
      secure:true,
      transports: ['websocket'],
      'force new connection': true});

    socket.on("connect", function () {
      console.log("Connected!");
    });
    socket.on("disconnect", function () {
      console.log("Disconnected!");
    });
    socket.on('notice.incoming', function (data) {
      event.sendNotification.dispatch(data);
    });
  }
  /**
   * Para mandar a cargar las notificaciones
   */
  var getNotifications=function(){
    var data = {
      skip:0,
      limit:10,
      status:0
    };
    $.ajax({
      type: 'get',
      url: url_server+'api/notifications/',
      data: data,
      headers: {
        "token":access_token
      },
      success: function (data) {
        event.getNotifications.dispatch(data);
      }
    });
  }
  /**
   * Para mandar a cargar las notificaciones
   */
  var deleteNotifications=function(user_id, notif_id, callback){
    var data = {
      id: notif_id
    };
    $.ajax({
      type: 'delete',
      url: url_server+'api/notifications/'+notif_id,
      headers: {
        "token":access_token
      },
      success: function (data) {
        callback();
      }
    });
  }

  /**
   * Crear los eventos
   */
  var createEvent=function(){
    event = {
     getNotifications: new Signal(),
     sendNotification: new Signal()
    }
  }
  return {
    //main function to initiate template pages
    init: function (config) {
      url_server=(config.url_server!='')?config.url_server:'';
      ws_url=(config.ws_url!='')?config.ws_url:'';
      //IMPORTANT!!!: Do not modify the call order.
      initializeServerComunication();
      createEvent();
      getNotifications();
    },
    sendNotification:function(){
      var pending=0;
      var data = {
        to:[$('#receiver_email_id').val()],
        pending:pending,
        metadata:[{'msg':$('#message_id').val()}]
      };
      $.ajax({
        type: 'post',
        url: url_server+'api/notifications/',
        data: data,
        headers: {
          "token":access_token
        },
        success: function (data) {
        }
      });
    },
    getEvent:function(){
      return event;
    },
    deleteNotifications: function(user_id, notif_id, callback){
      deleteNotifications(user_id, notif_id, callback);
    }
  };
}();
