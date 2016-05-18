/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2015.
 *========================================================================*/
var App = function () {
    var idWizardActive="1";

    /**
     * Para inicializar el wizard
     */
    var initializeWizard=function(){
        $('#rootwizard').bootstrapWizard({
            onNext: function (tab, navigation, index) {
                idWizardActive=(parseInt(index)+parseInt(1));
            },
            onPrevious: function (tab, navigation, index) {
                idWizardActive=(parseInt(index)-parseInt(1));
            },
            onTabShow:function(tab, navigation, index){
                if(index!=0){
                    $('#text-save').removeClass('hide');
                    $('.mcp-pager').removeClass('hide');
                }
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
            if(idWizardActive!=''){
                $('#tab' + idWizardActive).removeClass('active');
                $('#rootwizard').bootstrapWizard('show',(parseInt($(this).data("href"))-parseInt(1)));
                idWizardActive=$(this).data("href");
                $('#tab' + idWizardActive).addClass('active');
            }
            App.fix_height();
        })
    }
    return {
        //main function to initiate template pages
        init: function () {
            //IMPORTANT!!!: Do not modify the call order.
            initializeWizard();
            activeTabWizard();
            startStep();
        },
        fix_height:function(){
            $('.col-content').css("position", "absolute");
            var content_wizard = $('#content-wizard').height();
            $('.col-content').css("min-height", content_wizard + "px");
        }
    };
}()
//Start App
App.init();





