define([
    "jquery",
    "loadingPopup"
], function($){
    'use strict';

    $.awPopupManagerFieldset = {
        /*show or hide dependentSelector when headSelector =/!= values */
        addDependence: function(dependentSelector, headSelector, values)
        {
            jQuery(headSelector).change(function(){
                var me = this;
                var result = [];
                result = values.filter(function(el) {
                    return jQuery(me).val().indexOf(el) != -1
                });

                if (result.length == 0) {
                    jQuery(dependentSelector).hide();
                } else {
                    jQuery(dependentSelector).show();
                }
            });

            jQuery(document).ready(function(){
                var result = [];
                result = values.filter(function(el) {
                    return jQuery(headSelector).val().indexOf(el) != -1
                });
                if (result.length == 0) {
                    jQuery(dependentSelector).hide();
                } else {
                    jQuery(dependentSelector).show();
                }
            });
        },

        /*add or remove className to dependentSelector when headSelector =/!= values */
        addDependenceForClass: function(dependentSelector, className, headSelector, values)
        {
            jQuery(headSelector).change(function(){
                var me = this;
                var result = [];
                result = values.filter(function(el) {
                    return jQuery(me).val().indexOf(el) != -1
                });

                if (result.length == 0) {
                    jQuery(dependentSelector).removeClass(className);
                } else {
                    jQuery(dependentSelector).addClass(className);
                }
            });

            jQuery(document).ready(function(){
                var result = [];
                result = values.filter(function(el) {
                    return jQuery(headSelector).val().indexOf(el) != -1
                });
                if (result.length == 0) {
                    jQuery(dependentSelector).removeClass(className);
                } else {
                    jQuery(dependentSelector).addClass(className);
                }
            });
        }
    };

    return $.awPopupManagerFieldset;
});