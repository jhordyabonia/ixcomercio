define([
    "jquery",
    "awPopupMagnific"
], function($){
    'use strict';

    $.awPopupManager = {
        ajaxAddCookieUrl: null,
        cookiePregValue: 'aw_popup_viewed_popup_',
        formKeySelector: 'form_key',
        popupQueue : {
            isOpened : false,
            popups : {},
            showedPopups: [],

            /**
             * Check is mobile device
             *
             * @returns {boolean}
             */
            isMobile: function() {
                if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                    return true;
                }

                return false;
            },

            addPopup: function(key, popupInfo) {
                var queue = this;

                if ($.inArray(key, queue.showedPopups) >= 0) {
                    return;
                }

                queue.popups[key] = function(){
                    var isUsed = false;
                    $.magnificPopup.open({
                        items: {
                            src: popupInfo.content,
                            type: 'inline'
                        },

                        mainClass: popupInfo.effect + ' ' + popupInfo.position,
                        callbacks: {
                            open: function() {
                                var popupSelector = '.popup-content';

                                queue.isOpened = true;
                                if (popupInfo.preview) {
                                    return;
                                }
                                $.awPopupManager.sendCookie(key, 'aw_popup_viewed_popup', popupInfo.lifetime, true);
                                $(popupSelector).bind('copy', function(){
                                    if (!isUsed) {
                                        $.awPopupManager.sendCookie(key, 'aw_popup_used_popup', popupInfo.lifetime, true);
                                        isUsed = true;
                                    }
                                });
                                $(popupSelector + ' form').submit(function(){
                                    if (!isUsed) {
                                        $.awPopupManager.sendCookie(key, 'aw_popup_used_popup', popupInfo.lifetime, false);
                                        isUsed = true;
                                    }
                                });
                                $(popupSelector + ' a').click(function(){
                                    if (!isUsed) {
                                        $.awPopupManager.sendCookie(key, 'aw_popup_used_popup', popupInfo.lifetime, false);
                                        isUsed = true;
                                    }
                                });
                                $.awPopupManager._prepareFormKey($(popupSelector));
                                $(popupSelector).applyBindings();
                                $(popupSelector).trigger('contentUpdated');
                            },
                            close: function() {
                                queue.isOpened = false;
                                queue.removePopup(key);
                            },
                            afterClose: function() {
                                queue.openPopup();
                            },
                            beforeOpen: function() {
                                if (queue.isMobile()) {
                                    $('body').css('overflow', 'hidden');
                                }
                            },
                            beforeClose: function() {
                                if (queue.isMobile()) {
                                    $('body').css('overflow', 'auto');
                                }
                            }
                        }
                    });
                };
                if ($.magnificPopup.instance.isOpen){
                    $.magnificPopup.instance.st.callbacks.afterClose = function(){queue.openPopup()};
                } else {
                    queue.openPopup();
                }
            },

            removePopup: function(popupId) {
                delete this.popups[popupId];
                this.showedPopups.push(popupId);
            },

            //get first popup and show it
            openPopup: function() {
                if (this.isOpened == false) {
                    var popupId = Object.keys(this.popups)[0];
                    if (typeof(popupId) != 'undefined') {
                        this.popups[popupId].call();
                    }
                }
            }
        },

        initObserversForPopup : function(popups) {
            var me = this;
            $.each(popups, function(key, popup){
                if (me.popupQueue.isMobile()) {
                    popup.position = 'middle-center';
                }
                if (popup.preview) {
                    $(document).ready(function(){
                        me.popupQueue.addPopup(key, popup);
                    });
                    return;
                }
                if (me._getCookie(me.cookiePregValue + key) !== '') {
                    return;
                }
                switch (popup.event) {
                    case 'immediately_page_loaded':
                        $(document).ready(function(){
                            me.popupQueue.addPopup(key, popup);
                        });
                        break;
                    case 'x_sec_after_duration':
                        $(document).ready(function(){
                            setTimeout(function(){me.popupQueue.addPopup(key, popup)}, popup.event_value*1000);
                        });
                        break;
                    case 'once_page_scrolled':
                        $(window).scroll(function(){
                            if ($(document).scrollTop() / ($(document).height() - $(window).height())  * 100  >= popup.event_value) {
                                me.popupQueue.addPopup(key, popup);
                            }
                        });
                        break;
                    case 'once_customer_pages_viewed':
                        $(document).ready(function(){
                            me.popupQueue.addPopup(key, popup);
                        });
                        break;
                    case 'once_cursor_leave_browser':
                        addEvent(document, "mouseout", function(e) {
                            e = e ? e : window.event;
                            var from = e.relatedTarget || e.toElement;
                            if (!from || from.nodeName == "HTML") {
                                me.popupQueue.addPopup(key, popup);
                            }
                        });
                        break;
                }
            });
        },

        sendCookie: function(popupId, cookieType, cookieLifetime, async)
        {
            $.ajax({
                url: this.ajaxAddCookieUrl,
                type: "GET",
                dataType: 'json',
                async: async,
                context: this,
                data: {
                    isAjax: 'true',
                    cookie_type: cookieType,
                    popup_id: popupId,
                    cookie_lifetime: cookieLifetime
                }
            });
        },

        /**
         * Get cookie value
         *
         * @param cname
         * @returns {string}
         * @private
         */
        _getCookie: function(cname) {
            var name = cname + "=",
                decodedCookie = decodeURIComponent(document.cookie),
                ca = decodedCookie.split(';'),
                i, c;

            for(i = 0; i < ca.length; i++) {
                c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }

            return '';
        },

        /**
         * Prepare form keys for popup content
         *
         * @param contentElement
         * @private
         * @returns {Object}
         */
        _prepareFormKey: function (contentElement) {
            var self = this,
                formKeyInputs = $(contentElement).find('input[name="'+ this.formKeySelector + '"]');

            formKeyInputs.each(function (key, input) {
                $(input).val(self._getFormKey());
            });

            return contentElement;
        },

        /**
         * Get form key
         *
         * @returns {string}
         */
        _getFormKey: function () {
            if (!window.FORM_KEY) {
                window.FORM_KEY = this._getCookie(this.formKeySelector);
            }

            return window.FORM_KEY;
        }
    };

    /**
     * event for leave browser popup
     * see http://bradsknutson.com/blog/javascript-detect-mouse-leaving-browser-window/
     */
    function addEvent(obj, evt, fn) {
        if (obj.addEventListener) {
            obj.addEventListener(evt, fn, false);
        }
        else if (obj.attachEvent) {
            obj.attachEvent("on" + evt, fn);
        }
    }

    return $.awPopupManager;
});