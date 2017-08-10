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

        ], factory);
    } else {
        factory(window.jQuery);
    }
}(function ($) {
    'use strict';

    $.widget('salesigniter.sirentdebug', {
        options: {
            redirectUrl: ''
        },
        _create: function () {
            this._initCalls();
        },

        _initCalls: function () {
            var self = this;

            /**
             * Events listener
             */
            $(document).on('ajaxComplete', function (event, xhr, s) {
                var type = s.dataType;
                var ct = xhr.getResponseHeader("content-type"),
                    xml = type == "xml" || !type && ct && ct.indexOf("xml") >= 0,
                    data = xml ? xhr.responseXML : xhr.responseText;

                if (xml && data.documentElement.tagName == "parsererror")
                    throw "parsererror";

                // Allow a pre-filtering function to sanitize the response
                // s != null is checked to keep backwards compatibility
                if (s && s.dataFilter)
                    data = s.dataFilter(data, type);

                // The filter can actually parse the response
                if (typeof data === "string") {

                    // If the type is "script", eval it in global context
                    if (type == "script")
                        jQuery.globalEval(data);

                    // Get the JavaScript object, if JSON is used.
                    if (type == "json")
                        data = window["eval"]("(" + data + ")");
                }
                console.log('data here:' + data);
            });
        }

    });
    return {
        sirentdebug: $.salesigniter.sirentdebug
    };
}));
