define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'jquery/ui',
], function ($, modal, $t) {
    'use strict';

    /**
     * Component Ajax Compare.
     */
    $.widget('intcomex.ajaxCompare',{

        /**
         * Component options.
         */
        options: {
            popupWrapperSelector: '#mgp-compare-popup-wrapper',
            popupBlankSelector: '#mgp-compare-blank',
            closePopupModal: '.action-close',
            processStart: 'processStart',
            processStop : 'processStop',
            addToCompareButtonSelector: '.add-to-compare',
            addToCompareButtonDisabledClass: 'disabled',
            addToCompareButtonTextWhileAdding: '',
            addToCompareButtonTextAdded: '',
            addToCompareButtonTextDefault: '',
            btnCloseSelector: '#ajaxcompare_btn_close_popup',
            divMiniCompare: '#floatingComparisonBar',
            showLoader: true
        },

        /**
         * Create component.
         * @private
         */
         _create: function () {
            let self = this;
            self._init();
            $('body').on('contentUpdated', function () {
                self._init();
            });
        },

        /**
         * Init component.
         * @private
         */
        _init: function () {
            let self = this;
            $('body').on('click', self.options.btnCloseSelector, function (e) {
                self.closePopup();
            });

            self.element.find(self.options.addToCompareButtonSelector).off('click').click(function(e) {
                e.preventDefault();
                e.stopPropagation();
                self.addCompare($(this));
            });
        },

        /**
         * Show popup.
         */
        showPopup: function() {
            let self = this,
                comparePopup = $(self.options.popupWrapperSelector);
            let modaloption = {
                type: 'popup',
                modalClass: 'modal-popup_ajaxcompare_intcomex',
                responsive: true,
                innerScroll: true,
                clickableOverlay: true,
                closed: function(){
                   $('.modal-popup_ajaxcompare_intcomex').remove();
                }
            };
            modal(modaloption, comparePopup);
            comparePopup.modal('openModal');
        },

        /**
         * Close popup.
         */
        closePopup: function () {
            $(this.options.popupWrapperSelector).fadeOut('slow');
            $(this.options.popupBlankSelector).fadeOut('slow');
            $(this.options.closePopupModal).trigger('click');
        },

        /**
         * Add to compare ajax.
         * @param el
         */
        addCompare: function (el) {
            let self = this,
                body   = $('body'),
                parent = el.parent(),
                post   = el.data('post');
            let params = post.data;
            params['checked'] = el.is(':checked') ? 1 : 0;
            if(parent.hasClass(self.options.addToCompareButtonDisabledClass)) return;
            $.ajax({
                url: post.action,
                data: params,
                type: 'POST',
                dataType: 'json',
                showLoader: self.options.showLoader,
                beforeSend: function () {
                    self.disableAddToCompareButton(parent);
                    if (self.options.showLoader) body.trigger(self.options.processStart);
                },
                success: function (res) {
                    if (self.options.showLoader) body.trigger(self.options.processStop);
                    if (res.success) {
                        $(self.options.divMiniCompare).remove();
                        body.prepend(res.popup);
                        if (params['checked']) {
                            $(el).prop('checked', true);
                        } else {
                            $(el).prop('checked', false);
                        }
                    } else if (!res.success) {
                        body.append('<div class="mgp-compare-popup-wrapper" id="' + self.options.popupWrapperSelector.replace(/^#/, "") +'" >'+res.popup+'</div>');
                        self.showPopup();
                    } else {
                        alert($t('No response from server'));
                    }
                }
            }).done(function() {
                 self.enableAddToCompareButton(parent);
            });
        },

        /**
         * Disable compare button while adding product.
         * @param {String} form
         */
        disableAddToCompareButton: function (form) {
            let addToCompareButtonTextWhileAdding = this.options.addToCompareButtonTextWhileAdding || $t('Adding...'),
                addToCompareButton = $(form).find(this.options.addToCompareButtonSelector);

            addToCompareButton.addClass(this.options.addToCompareButtonDisabledClass);
            addToCompareButton.find('span').text(addToCompareButtonTextWhileAdding);
            addToCompareButton.attr('title', addToCompareButtonTextWhileAdding);
        },

        /**
         * Enable compare button when finished adding product.
         * @param {String} form
         */
        enableAddToCompareButton: function (form) {
            let addToCompareButtonTextAdded = this.options.addToCompareButtonTextAdded || $t('Added'),
                self = this,
                addToCompareButton = $(form).find(this.options.addToCompareButtonSelector);

            addToCompareButton.find('span').text(addToCompareButtonTextAdded);
            addToCompareButton.attr('title', addToCompareButtonTextAdded);

            setTimeout(function () {
                let addToCompareButtonTextDefault = self.options.addToCompareButtonTextDefault || $t('Add to Compare');

                addToCompareButton.removeClass(self.options.addToCompareButtonDisabledClass);
                addToCompareButton.find('span').text(addToCompareButtonTextDefault);
                addToCompareButton.attr('title', addToCompareButtonTextDefault);
            }, 1000);
        }

    });

    return $.intcomex.ajaxCompare;

});
