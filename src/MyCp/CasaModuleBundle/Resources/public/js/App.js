/*========================================================================
 * App script to handle the entire CasaModule and base functions
 * Copyright 2015.
 *========================================================================*/
var App = function () {

    var idWizardActive="0";
    var textHelp="";
    var Signal = signals.Signal;
    var event='';
    var idown='';
    var flag='';

    /**
     * Crear los eventos
     */
    var createEvent=function(){
        event = {
            clickBtnContinueAfter: new Signal()
        }
    }
    /**
     * Para inicializar el wizard
     */
    var initializeWizard=function(){
        $('#rootwizard').bootstrapWizard({
            onNext: function (tab, navigation, index) {
                event.clickBtnContinueAfter.dispatch(index);
            },
            onPrevious: function (tab, navigation, index) {
            },
            onTabShow:function(tab, navigation, index){
                $('#li'+idWizardActive).removeClass('active');
                $('#li'+index).addClass('active');
                idWizardActive=index;
                App.hide_pagination_izard(index);

                var stepsCount = navigation.find('li').length-1;

                if(index == stepsCount)
                {
                    $("#btnNext").addClass("hidden");
                    $("#btnPublish").removeClass("hidden");
                }
                else{
                    $("#btnNext").removeClass("hidden");
                    $("#btnPublish").addClass("hidden");
                }
            }
        });
        $('#steps').addClass('hide');
        idown=$('#page-wrapper').data("idown");
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
     * Para cuando se selecciona un link del menu
     */
    var activeTabWizard=function(){
        //Para cuando se selecciona un link del menu
        jQuery('.sidebar-collapse').on('click', ' li > a.ajaxify', function (e) {
            e.preventDefault();
            //event.clickBtnContinueAfter.dispatch(idWizardActive);
            $('#tab' + idWizardActive).removeClass('active');
            $('#rootwizard').bootstrapWizard('show',$(this).data("href"));
            idWizardActive=$(this).data("href");
            //App.fix_height();
        })
    }
    /**
     * Para cuando el usuario de click en la ayuda
     */
    var mouseHelp=function(){
        $('.help-icon').on('click mouseover',function(){
            if(textHelp!=''){
                $('#'+textHelp).css('background-color','transparent');
                $('#'+$(this).data("href")).css('background-color','#e94b3d');
            }
            else
                $('#'+$(this).data("href")).css('background-color','#e94b3d');

            textHelp=$(this).data("href");
        })
    }
    /**
     * Para salvar los diferentes step
     */
    var saveStep=function(){
        $('#saveStep').on('click',function(){
            ajaxControllers();
            event.clickBtnContinueAfter.dispatch(idWizardActive);

        })
    }
    /**
     * Para cuando se da click en el boton de cambiar
     */
    var changeBtn=function(){
        $('.changeBtn').on('click',function(){
            $($(this).data("cmpdisabled")).addClass('hide');
            $($(this).data("cmpenabled")).removeClass('hide');
            $(this).addClass('hide');
        });
        $('.changeBtn1').on('click',function(){
            $($(this).data("cmpdisabled")).addClass('hide');
            $($(this).data("cmpenabled")).removeClass('hide');
        });
    }
    /**
     * Funcion para validar la entrada de numeros
     */
    var validateInteger=function(){
        $('.only-number').keyup(function (){
            this.value = (this.value + '').replace(/[^0-9]/g, '');
        });
    }
    /**
     * Funcion para inicializar el menu
     */
    var initializeMenu=function(){
        if (localStorageSupport){
            if(localStorage.getItem("permited_click_menu_two_level")==null){
                localStorage.setItem("permited_click_menu_two_level",true);
            }
        }
    }
    var showAction=function(){
        //Para cuando se selecciona un link del menu
        jQuery('.sidebar-collapse').on('click', ' li > a.redirect', function (e) {
            if (localStorageSupport){
                if(localStorage.getItem("permited_click_menu_two_level")=="true"){
                    localStorage.setItem("permited_click_menu_two_level",false);
                    window.location.href = $(this).data('href');
                }
            }
        });
    }
    var showNormal=function(){
        //Para cuando se selecciona un link del menu
       jQuery('.sidebar-collapse').on('click', ' li > a.normal', function (e) {
            localStorage.setItem("permited_click_menu_two_level",true);
            return true;
        });
    }
    var countAjax=0;
    var ajaxControllers= function(){
        $(document).ajaxSend(function (event, jqXHR, ajaxOptions) {
            if (ajaxOptions.dataType != 'script') {
            countAjax++;
            }
        });
        $(document).ajaxComplete(function () {
            countAjax--;
            if(countAjax==0) window.location=logoutUrl;
        });
        $(document).ajaxError(function () {
            countAjax--;if(countAjax==0) window.location=logoutUrl;
        });
    }
    /**
     * Funcion que devuelve el tipo de valor
     * @param obj
     * @returns {*}
     */
    var toType = function (obj) {
        if (typeof obj === "undefined") {
            return "undefined";

            // consider: typeof null === object
        }
        if (obj === null) {
            return "null";
        }

        var type = Object.prototype.toString.call(obj).match(/^\[object\s(.*)\]$/)[1] || '';

        switch (type) {
            case 'Number':
                if (isNaN(obj)) {
                    return "nan";
                } else {
                    return "number";
                }
            case 'String':
            case 'Boolean':
            case 'Array':
            case 'Date':
            case 'RegExp':
            case 'Function':
                return type.toLowerCase();
        }
        if (typeof obj === "object") {
            return "object";
        }
        return undefined;
    }
    return {
        //main function to initiate template pages
        init: function () {
            //IMPORTANT!!!: Do not modify the call order.
            initializeWizard();
            activeTabWizard();
            startStep();
            mouseHelp();
            saveStep();
            createEvent();
            changeBtn();
            validateInteger();
            initializeMenu();
            showAction();
            showNormal();
        },
        initializePlugins:function(selector,color){
            var elems = Array.prototype.slice.call(document.querySelectorAll((typeof(selector) === "undefined")?'.js-switch':selector));
            elems.forEach(function(html) {
                var switchery = new Switchery(html, { color: (typeof(color) === "undefined")?'#58ae17':color });
            });
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
        },
        getEvent:function(){
            return event;
        },
        getOwnId:function(){
            return idown;
        },
        fireEventSaveTab: function (){
            event.clickBtnContinueAfter.dispatch(idWizardActive);
        },
        equals : function () {

            var innerEquiv; // the real equiv function
            var callers = []; // stack to decide between skip/abort functions
            var parents = []; // stack to avoiding loops from circular referencing
            // Call the o related callback with the given arguments.

            function bindCallbacks(o, callbacks, args) {
                var prop = toType(o);
                if (prop) {
                    if (toType(callbacks[prop]) === "function") {
                        return callbacks[prop].apply(callbacks, args);
                    } else {
                        return callbacks[prop]; // or undefined
                    }
                }
            }

            var callbacks = function () {

                // for string, boolean, number and null

                function useStrictEquality(b, a) {
                    if (b instanceof a.constructor || a instanceof b.constructor) {
                        // to catch short annotaion VS 'new' annotation of a
                        // declaration
                        // e.g. var i = 1;
                        // var j = new Number(1);
                        return a == b;
                    } else {
                        return a === b;
                    }
                }

                return {
                    "string": useStrictEquality,
                    "boolean": useStrictEquality,
                    "number": useStrictEquality,
                    "null": useStrictEquality,
                    "undefined": useStrictEquality,

                    "nan": function (b) {
                        return isNaN(b);
                    },

                    "date": function (b, a) {
                        return QUnit.toType(b) === "date" && a.valueOf() === b.valueOf();
                    },

                    "regexp": function (b, a) {
                        return QUnit.toType(b) === "regexp" && a.source === b.source && // the regex itself
                            a.global === b.global && // and its modifers
                            // (gmi) ...
                            a.ignoreCase === b.ignoreCase && a.multiline === b.multiline;
                    },

                    // - skip when the property is a method of an instance (OOP)
                    // - abort otherwise,
                    // initial === would have catch identical references anyway
                    "function": function () {
                        var caller = callers[callers.length - 1];
                        return caller !== Object && typeof caller !== "undefined";
                    },

                    "array": function (b, a) {
                        var i, j, loop;
                        var len;

                        // b could be an object literal here
                        if (!(toType(b) === "array")) {
                            return false;
                        }

                        len = a.length;
                        if (len !== b.length) { // safe and faster
                            return false;
                        }

                        // track reference to avoid circular references
                        parents.push(a);
                        for (i = 0; i < len; i++) {
                            loop = false;
                            for (j = 0; j < parents.length; j++) {
                                if (parents[j] === a[i]) {
                                    loop = true; // dont rewalk array
                                }
                            }
                            if (!loop && !innerEquiv(a[i], b[i])) {
                                parents.pop();
                                return false;
                            }
                        }
                        parents.pop();
                        return true;
                    },

                    "object": function (b, a) {
                        var i, j, loop;
                        var eq = true; // unless we can proove it
                        var aProperties = [],
                            bProperties = []; // collection of
                        // strings
                        // comparing constructors is more strict than using
                        // instanceof
                        if (a.constructor !== b.constructor) {
                            return false;
                        }

                        // stack constructor before traversing properties
                        callers.push(a.constructor);
                        // track reference to avoid circular references
                        parents.push(a);

                        for (i in a) { // be strict: don't ensures hasOwnProperty
                            // and go deep
                            loop = false;
                            for (j = 0; j < parents.length; j++) {
                                if (parents[j] === a[i]) loop = true; // don't go down the same path
                                // twice
                            }
                            aProperties.push(i); // collect a's properties
                            if (!loop && !innerEquiv(a[i], b[i])) {
                                eq = false;
                                break;
                            }
                        }

                        callers.pop(); // unstack, we are done
                        parents.pop();

                        for (i in b) {
                            bProperties.push(i); // collect b's properties
                        }

                        // Ensures identical properties name
                        return eq && innerEquiv(aProperties.sort(), bProperties.sort());
                    }
                };
            }();

            innerEquiv = function () { // can take multiple arguments
                var args = Array.prototype.slice.apply(arguments);
                if (args.length < 2) {
                    return true; // end transition
                }

                return (function (a, b) {
                    if (a === b) {
                        return true; // catch the most you can
                    } else if (a === null || b === null || typeof a === "undefined" || typeof b === "undefined" || toType(a) !== toType(b)) {
                        return false; // don't lose time with error prone cases
                    } else {
                        return bindCallbacks(a, callbacks, [b, a]);
                    }

                    // apply transition with (1..n) arguments
                })(args[0], args[1]) && arguments.callee.apply(this, args.splice(1, args.length - 1));
            };

            return innerEquiv;

        }()
    };
}()
//Start App
App.init();
App.initializePlugins();





