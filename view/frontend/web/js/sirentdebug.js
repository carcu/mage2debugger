/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/*jshint browser:true jquery:true */
/*eslint max-depth: 0*/

(function (factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'css!css/uidialog/dialog',
            'css!css/uiaccordion/accordion',
            'jquery/ui'
        ], factory);
    } else {
        factory(window.jQuery);
    }
}(function ($) {
    'use strict';

    /**
     * debuggerpanel dialog
     */

    var widthN = 500;//$(window).width();
    var heightN = 800;//$(window).height();
    var dialogN = $("<div></div>").append($('#debuggerPanel')).appendTo("body").dialog({
        autoOpen: true,
        modal: false,
        resizable: false,
        dialogClass: 'debuggerdialog',
        // classes: {
        //   "ui-dialog": "debuggerdialog"
        //},
        width: widthN,
        height: heightN,
        close: function () {
            $(this).dialog('destroy');
        }
    });

    $('.debuggerAccordion').accordion({
        heightStyle: 'fill',
        classes: {
            "ui-accordion": "debuggerAccordion"
        }
    });
    $('.debuggerAccordionsSub').accordion({
        heightStyle: 'content',
        classes: {
            "ui-accordion": "debuggerAccordion"
        }
    });
    $.ajaxPrefilter(/*dataTypes, */ function global_ajaxPrefilter(options, originalOptions, jqXHR) {
        jqXHR.done(function global_ajaxSuccess(data, textStatus, jqXHR) {
            //console.groupCollapsed(options.url + (options.data ? '&' + $.param(options.data) : ''));
            //console.log("Options: " + JSON.stringify(options));
            //console.log("Data: " + jqXHR.responseText);
            //console.groupEnd();
            if (data['debuggerData']) {
                $('.debuggerAccordion').accordion('destroy');
                $('#debuggerPanel').html(data['debuggerData']);
                $('.debuggerAccordion').accordion({
                    heightStyle: 'fill',
                    classes: {
                        "ui-accordion": "debuggerAccordion"
                    }
                });
                $('.debuggerAccordionsSub').accordion({
                    heightStyle: 'content',
                    classes: {
                        "ui-accordion": "debuggerAccordion"
                    }
                });
                dialogN.dialog("option", "position", {my: "center", at: "center", of: window});
            }
        });
        jqXHR.fail(function global_ajaxError(jqXHR, textStatus, errorThrown) {
            if (jqXHR.responseText.indexOf('Whoops! There was an error.') > 0) {
                //$('#sirentdiv').data('htmldata', jqXHR.responseText);
                var errorArray = jqXHR.responseText.split(';;;');
                //$('#sirenta').trigger('click');
                if (errorArray[1]) {
                    $('body').trigger('processStop');
                    //console.log(errorArray[1]);
                    var iframe = $('<iframe frameborder="0" marginwidth="0" marginheight="0" allowfullscreen></iframe>');
                    var src = errorArray[1];
                    var title = 'Error';
                    var width = 1024;//$(window).width();
                    var height = 800;//$(window).height();
                    iframe.attr({
                        width: +width,
                        height: +height,
                        src: src
                    });

                    var dialog = $("<div></div>").append(iframe).appendTo("body").dialog({
                        autoOpen: false,
                        modal: false,
                        resizable: true,
                        dialogClass: 'debuggerdialog',//
                        width: width,
                        height: height,
                        close: function () {
                            $(this).dialog('destroy');
                            iframe.attr("src", "");
                        }
                    });


                    dialog.dialog("option", "title", title).dialog("open");
                    //var wi = window.open(errorArray[1], '_blank');
                    //wi.location.href = errorArray[1];
                }
                //wi.document.writeln($('#sirentdiv').data('htmldata'));
            }

        });
    });
}));
