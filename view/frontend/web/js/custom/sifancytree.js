/*
 * Copyright © 2017 Magento. All rights reserved.
 * See https://rentalbookingsoftware.com/license.html for license details.
 *
 */

/*jshint jquery:true*/
define([
    'jquery',
    'jquery/ui',
    'cssd!css/fancytree/skin-win8/ui.fancytree.css',
    'fancytreeall'
], function ($) {
    'use strict';

    $.widget('salesigniter_debugger.sifancytree', {
        options: {
            url: null,
            classElementTrigger: null,
        },

        /**
         * Bind a click handler on the widget's element.
         * @private
         */
        _create: function () {
            //this.element.on('click', $.proxy(this._clickAction, this));
            //$(this.options.classElementTriggerAjax).on('click', $.proxy(this._clickAction, this));
            //console.log('ff' + this.options.classElementTrigger);
            $(this.options.classElementTrigger).fancytree({
                autoActivate: false, // we use scheduleAction()
                autoCollapse: true,
//			autoFocus: true,
                autoScroll: true,
                clickFolderMode: 3, // expand with single click
                minExpandLevel: 2,
                tabindex: "-1", // we don't want the focus frame
                // toggleEffect: { effect: "blind", options: {direction: "vertical", scale: "box"}, duration: 2000 },
                // scrollParent: null, // use $container
                focus: function (event, data) {
                    var node = data.node;
                    // Auto-activate focused node after 1 second
                    if (node.data.href) {
                        node.scheduleAction("activate", 1000);
                    }
                },
                blur: function (event, data) {
                    data.node.scheduleAction("cancel");
                },
                beforeActivate: function (event, data) {
                    var node = data.node;

                    if (node.data.href && node.data.target === "_blank") {
                        //window.open(node.data.href, "_blank");
                        //return false; // don't activate
                    }
                },
                activate: function (event, data) {
                    var node = data.node,
                        orgEvent = data.originalEvent || {};

                    // Append #HREF to URL without actually loading content
                    // (We check for this value on page load re-activate the node.)
                    if (window.parent && parent.history && parent.history.pushState) {
                        //parent.history.pushState({title: node.title}, "", "#" + (node.data.href || ""));
                    }
                },
                click: function (event, data) {
                    // We implement this in the `click` event, because `activate` is not
                    // triggered if the node already was active.
                    // We want to allow re-loads by clicking again.
                    var node = data.node,
                        orgEvent = data.originalEvent;

                    // Open href (force new window if Ctrl is pressed)
                    if (!node.isActive() && node.data.href) {
                        //$.get(node.data.href, function( data ) {
                        //$('.salesigniter-debugger-showlogsnav-index .column.main').css('height', '100%');
                        $('#sicontent').find('iframe').prop('src', node.data.href);
                        //});
                    }
                }
            });
            // On page load, activate node if node.data.href matches the url#href
            var tree = $(":ui-fancytree").fancytree("getTree"),
                frameHash = window.parent && window.parent.location.hash;

            if (frameHash) {
                frameHash = frameHash.replace("#", "");
                tree.visit(function (n) {
                    if (n.data.href && n.data.href === frameHash) {
                        n.setActive();
                        return false; // done: break traversal
                    }
                });
            }
        },

        /**
         * Init object
         * @private
         */
        _init: function () {
            // Do something if needed
        },

        /**
         * Check action function
         * @private
         * @param event - {Object} - Click event.
         */
        _checkQtyAvailable: function (event) {
            // Do something with element selected $(event.target)
            var dataFormSerializedAsArray = $(event.target).find(':input').serializeArray();
            //dataFormSerializedAsArray.push({
            //name: "product_id_orig",
            //value: $(event.target).attr('value_attr')
            //});

            //todo here storage can be used
            $.ajax({
                url: this.options.url,
                data: $.param(dataFormSerializedAsArray),
                type: 'post',
                dataType: 'json',
                beforeSend: function () {
                    $('body').trigger('processStart');

                },
                success: function (res) {
                    $('body').trigger('processStop');

                    $('.qty_available').html(res.availableQuantity);
                }
            });
        },

        /**
         * Click action function
         * @private
         * @param event - {Object} - Click event.
         */
        _clickAction: function (event) {
            // Do something with element clicked $(event.target)
        }
    });

    return $.salesigniter_debugger.sifancytree;
});
