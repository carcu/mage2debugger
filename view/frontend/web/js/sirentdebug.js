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
            'cssd!css/uidialog/dialog',
            'cssd!css/uiaccordion/accordion',
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
    if ($('#debuggerPanel').length) {
        var widthN = 500;//$(window).width();
        var heightN = 800;//$(window).height();
        var dialogN = $("<div id='dialogN'></div>").append($('#debuggerPanel')).appendTo("body").dialog({
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
        dialogN.dialog("option", "position", {my: "right bottom", at: "right+200 bottom+100", of: "body"});
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
    }


}));
