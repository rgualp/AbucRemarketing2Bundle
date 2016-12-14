/**
 * Created by mgleon on 7/09/2015.
 */
$(function () {
    hds = {};
    hds.msg = (function () {
        var indexsTypes = {
            success: 0,
            info: 1,
            warning: 2,
            error: 3
        };
        var errorsTypes = [
            {
                type:'success',
                title:'Success',
                css:'',
                conf:function(){
                    toastr.options.timeOut = "3000";
                    toastr.options.extendedTimeOut = "1000";
                }
            }, {
                type:'info',
                title:'Info',
                css:'',
                conf:function(){
                    toastr.options.timeOut = "-1";
                    toastr.options.extendedTimeOut = "1000";
                }
            }, {
                type:'warning',
                title:'Warning',
                css:'',
                conf:function(){
                    toastr.options.timeOut = "-1";
                    toastr.options.extendedTimeOut = "1000";
                }
            }, {
                type:'error',
                title:'Error',
                css:'',
                conf:function(){
                    toastr.options.timeOut = "-1";
                    toastr.options.extendedTimeOut = "-1";
                }
            }];

        return {
            show : function(indexType, msg, title){
                indexType = $.isNumeric(indexType) ? indexType : indexsTypes[indexType];

                toastr.options = {
                    "closeButton": true,
                    "debug": false,
                    "newestOnTop": false,
                    "progressBar": false,
                    "positionClass": "toast-top-right",
                    "preventDuplicates": false,
                    "onclick": null,
                    "showDuration":"300",
                    "hideDuration": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
                errorsTypes[indexType].conf();

                if(title === false){
                    title = '';
                }
                else{
                    title = title ? title : errorsTypes[indexType].title;
                }

                var $toastr = toastr[errorsTypes[indexType].type](msg, title);

                var html = '<div class="'+ errorsTypes[indexType].css +'" style="position: absolute; margin-top: 15px; margin-left: -5px; font-size: 32px;"></div>';
                $(html).insertBefore($toastr.find('.toast-title'));

                return $toastr;
            }
        }
    })();

    sabrus = hds;
    //hds.msg.show(0, 'Xxfsdfsdfsdfsdf');
});