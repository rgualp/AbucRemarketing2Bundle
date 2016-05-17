/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2015.
 *========================================================================*/
/**

 **/
var App = function () {
    var idWizardActive="wizard-p-0";

    /**
     * Para activar el wizard
     */
    var initializeWizard=function(){
        $("#wizard").steps();
        $('.steps').addClass('hide');
    }
    /**
     * Para cunado se da click en el boton comenzar
     */
    var startStep=function(){
       $('#btn-start').on('click',function(){
           $('#content-wizard').removeClass('hide');
           $('#step-0').css('display', 'none');
           $('#wizard').css('display', 'block');
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
                $('#step-0').css('display', 'none');
                $('#wizard').css('display', 'block');

                $('#' + idWizardActive).css('display', 'none');
                $('#' + $(this).data("href")).css('display', 'block');
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
        }
    };
}();
App.init();





