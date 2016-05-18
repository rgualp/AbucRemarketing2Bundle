/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2015.
 *========================================================================*/
/**

 **/
var App = function () {
    var idWizardActive="tab1";

    /**
     * Para inicializar el wizard
     */
    var initializeWizard=function(){
        $('#rootwizard').bootstrapWizard({
            onNext: function (tab, navigation, index) {
                idWizardActive='tab'+(parseInt(index)+parseInt(1));
            },
            onPrevious: function (tab, navigation, index) {
                idWizardActive='tab'+(parseInt(index)-parseInt(1));
            }
        });
        $('#steps').addClass('hide');
    }
    /**
     * Para cunado se da click en el boton comenzar
     */
    var startStep=function(){
       $('#btn-start').on('click',function(){
           $('#content-wizard').removeClass('hide');
           $('#step-0').addClass('hide');
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
                //Inicializar el wizard
                $('#content-wizard').removeClass('hide');
                $('#step-0').addClass('hide');
                App.fix_height();
                $('#' + idWizardActive).removeClass('active');
                $('#rootwizard').show('tab1');
                //$('#' + $(this).data("href")).addClass('active');
                idWizardActive=$(this).data("href");
            }
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
            var navbarHeigh = $('nav.navbar-default').height();
            var wrapperHeigh = $('#col-content').height();

            if (navbarHeigh > wrapperHeigh) {
                $('#col-content').css("min-height", navbarHeigh + "px");
            }

            if (navbarHeigh < wrapperHeigh) {
                $('#col-content').css("min-height", $(window).height() + "px");
            }

            if ($('body').hasClass('fixed-nav')) {
                $('#col-content').css("min-height", $(window).height() - 60 + "px");
            }
        }
    };
}();
App.init();





