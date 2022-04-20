/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_Breadcrumbs
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'ko',
    'Bss_SeoCore/js/model/mess'
],function ($, ko, mess) {
    'use strict';

    var mixin = {
        templateNotice: 'Bss_SeoCore/grid/listing',

        /**
         * @inheritDoc
         */
        initialize: function () {
            var self = this;
            mess.hasMess.subscribe(function (hasMess) {
                self.hasBreadcrumbsWarning(hasMess);
            }.bind(this));
            mess.mess.subscribe(function (mess) {
                self.breadcrumbsWarning(mess);
            }.bind(this));
            return this._super();
        },
        /**
         * @inheritDoc
         */
        initObservable: function () {
            this._super().observe([
                'hasBreadcrumbsWarning',
                'breadcrumbsWarning'
            ]);

            return this;
        },
        /**
         * @inheritDoc
         */
        getTemplate: function () {
            console.log(this._super());
            return this.templateNotice !== undefined ? this.templateNotice : this._super();
        },
        /**
         * @inheritDoc
         */
        dismissAll: function () {
            this.hasBreadcrumbsWarning = false;
            $('.bss-message-system').remove();
            return this._super();
        }
    }

    return function (listingComponent) {
        return listingComponent.extend(mixin);
    }
});
