/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2015.
 *========================================================================*/
var App = function () {
    var idWizardActive="0";
    var textHelp="";
    /**
     * Para inicializar el wizard
     */
    var initializeWizard=function(){
        $('#rootwizard').bootstrapWizard({
            onNext: function (tab, navigation, index) {
            },
            onPrevious: function (tab, navigation, index) {
            },
            onTabShow:function(tab, navigation, index){
                $('#li'+idWizardActive).removeClass('active');
                $('#li'+index).addClass('active');
                idWizardActive=index;
                App.hide_pagination_izard(index);
            }
        });
        $('#steps').addClass('hide');
    }
    /**
     * Para cunado se da click en el boton comenzar
     */
    var startStep=function(){
       $('#btn-start').on('click',function(){
           $('#rootwizard').bootstrapWizard('show',1);
           App.fix_height();
       })
    }
    /**
     * //Para cuando se selecciona un link del menu
     */
    var activeTabWizard=function(){
        //Para cuando se selecciona un link del menu
        jQuery('.sidebar-collapse').on('click', ' li > a.ajaxify', function (e) {
            e.preventDefault();
            $('#tab' + idWizardActive).removeClass('active');
            $('#rootwizard').bootstrapWizard('show',$(this).data("href"));
            idWizardActive=$(this).data("href");
            App.fix_height();
        })
    }
    /**
     * //Para cuando el usuario de click en la ayuda
     */
    var mouseHelp=function(){
        $('.help-icon').on('click',function(){
            if(textHelp!=''){
                $('#'+textHelp).css('background-color','transparent');
                $('#'+$(this).data("href")).css('background-color','#e94b3d');
            }
            else
                $('#'+$(this).data("href")).css('background-color','#e94b3d');

            textHelp=$(this).data("href");
        })
    }
    return {
        //main function to initiate template pages
        init: function () {
            //IMPORTANT!!!: Do not modify the call order.
            initializeWizard();
            activeTabWizard();
            startStep();
            mouseHelp();
        },
        fix_height:function(){
           /* $('.col-content').css("position", "absolute");
            var content_wizard = $('#content-wizard').height();
            $('.col-content').css("min-height", content_wizard + "px");*/
        },
        hide_pagination_izard:function(index){
            if(index==0){
                $('#text-save').addClass('hide');
                $('.mcp-pager').addClass('hide');
            }
            else{
                $('#text-save').removeClass('hide');
                $('.mcp-pager').removeClass('hide');
            }
        }
    };
}()
//Start App
App.init();





