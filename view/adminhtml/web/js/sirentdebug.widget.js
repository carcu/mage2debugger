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

            $.ajaxPrefilter(/*dataTypes, */ function global_ajaxPrefilter(options, originalOptions, jqXHR) {
                console.log('inited');
                jqXHR.done(function global_ajaxSuccess(data, textStatus, jqXHR) {
                    //console.groupCollapsed(options.url + (options.data ? '&' + $.param(options.data) : ''));
                    //console.log("Options: " + JSON.stringify(options));
                    console.log("Data: " + JSON.stringify(data));
                    //console.groupEnd();
                });
                jqXHR.fail(function global_ajaxError(jqXHR, textStatus, errorThrown) {
                    console.log(textStatus + ": " + errorThrown);
                    console.log(jqXHR.responseText);
                });
            });
        }

    });
    return {
        sirentdebug: $.salesigniter.sirentdebug
    };
}));
