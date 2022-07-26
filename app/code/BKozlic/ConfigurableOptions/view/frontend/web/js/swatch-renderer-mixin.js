/**
 * @category  BKozlic
 * @package   BKozlic\ConfigurableOptions
 * @author    Berin Kozlic - berin.kozlic@gmail.com
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
    'jquery',
    'underscore',
    'BKozlic_ConfigurableOptions/js/model/get-async-attribute-values'
], function ($, _, getAsyncValues) {
    'use strict';

    let swatchRendererMixin = {
        _init: function () {
            this.options.gallerySwitchStrategy = this.options.jsonConfig.gallerySwitchStrategy;
            this._super();
            $(this).on('swatchPriorityItemsReady', function(event, widget){
                widget._preselect();
            });
            this._eventPriorityItemsReady();
        },

        _OnClick: function ($this, $widget, eventName) {
            this._super($this, $widget, eventName);
            this._updateSimpleProductAttributes();
        },

        _OnChange: function ($this, $widget) {
            this._super($this, $widget);
            this._updateSimpleProductAttributes();
        },

        _eventPriorityItemsReady : function(){
            let widget = this,
                productPrice;

            if(!widget.options.jsonConfig.preselectEnabled){
                return false;
            }
            productPrice = widget.element.parents(widget.options.selectorProduct)
                .find(widget.options.selectorProductPrice);
            if(!productPrice.length){
                productPrice = $(widget.options.selectorProduct).find(widget.options.selectorProductPrice).length ?
                    $(widget.options.selectorProduct).find(widget.options.selectorProductPrice) :
                    $('.product-info_main').find(widget.options.selectorProductPrice);
            }
            if(!productPrice.length){
                return false;
            }
            let interval = setInterval(function(){
                if (productPrice.is(':data(mage-priceBox)')){
                    clearInterval(interval);
                    $(widget).trigger("swatchPriorityItemsReady",[widget]);
                }
            },500);
        },

        /**
         * Preselect configurable product options
         * @private
         */
        _preselect: function () {
            let widget = this,
                options = this.options,
                preselectEnabled = options.jsonConfig.preselectEnabled,
                simpleProduct = options.jsonConfig.simpleProduct;

            if (!preselectEnabled) {
                return false;
            }
            widget._preselectProduct(simpleProduct);
        },

        /**
         * Preselect specific product if set
         * @param simpleProduct
         * @private
         */
        _preselectProduct: function (simpleProduct) {
            let widget = this,
                classes = widget.options.classes,
                selectOptions = this.options.jsonConfig.index[simpleProduct];

            if (!selectOptions) {
                this._preselectFirstOption(this.options.jsonConfig.index);
                return false;
            }
            $.each(selectOptions, function (index, value) {
                let attributeId = index,
                    optionId = value,
                    $wrapper,
                    $optionsWrapper,
                    optIdKey = '';

                $wrapper = $(widget.element.context).find('.' + classes.attributeClass + '[attribute-id="' + attributeId + '"]');
                if (!$wrapper.length) {
                    $wrapper = $(widget.element.context).find('.' + classes.attributeClass + '[data-attribute-id="' + attributeId + '"]');
                }
                $optionsWrapper = $wrapper.find('.' + classes.attributeOptionsWrapper);
                if ($optionsWrapper.children().is('div')) {
                    optIdKey =  'option-id';
                    let $optionElement = $wrapper.find('.' + classes.optionClass + '[' + optIdKey + '="' + optionId + '"]');
                    if (!$optionElement.length) {
                        optIdKey = 'data-option-id';
                        $optionElement = $wrapper.find('.' + classes.optionClass + '[' + optIdKey + '="' + optionId + '"]');
                    }
                    if($optionElement.length > 1){
                        let code = widget.options.jsonConfig.mappedAttributes[index].code;
                        let divId = 'option-label-' + code + '-' + index + '-item-' + value;
                        $optionElement = $wrapper.find('.' + classes.optionClass + '[' + optIdKey + '="' + optionId + '"][id="' + divId +'"]');
                    }
                    $optionElement.click();
                } else {
                    let $select = $optionsWrapper.find('select'),
                        $optionElement = $optionsWrapper.find('select option[option-id="' + optionId + '"]');

                    $select.val($optionElement.val());
                    $select.change();
                }
            });
        },

        /**
         * Preselect first not disabled options of configurable product
         * @private
         */
        _preselectFirstOption: function (simpleProducts) {
            let widget = this,
                simpleProductId = Object.keys(simpleProducts).shift();

            if (typeof(simpleProductId) === "undefined" || simpleProductId === ''){
                return false;
            }
            widget._preselectProduct(simpleProductId);
        },

        /**
         * Update simple product attribute values
         * @private
         */
        _updateSimpleProductAttributes: function () {
            let widget = this,
                updateEnabled = widget.options.jsonConfig.attributesUpdateEnabled,
                options = _.object(_.keys(widget.optionsMap), {}),
                key;

            widget.element.find('.' + widget.options.classes.attributeClass).each(function () {
                if (!$(this).attr('data-option-selected') && !$(this).attr('option-selected')) {
                    return;
                }

                let attributeId = $(this).attr('data-attribute-id') || $(this).attr('attribute-id'),
                    selectedValue = $(this).attr('data-option-selected') || $(this).attr('option-selected');

                if (selectedValue) {
                    options[attributeId] = selectedValue.toString();
                }
            });

            if (!updateEnabled) {
                return false;
            }

            key = _.findKey(widget.options.jsonConfig.index, options);
            if (!key) {
                return false;
            }

            this._updateAttributeValuesFromJson(key);
            this._updateAttributeValuesAsynchronously(key);
        },

        /**
         * Update simple product attribute values from json
         * @param productId
         * @private
         */
        _updateAttributeValuesFromJson: function (productId) {
            let attributesForUpdate = this.options.jsonConfig.attributesForUpdate;
            if (!attributesForUpdate) {
                return false;
            }

            let content = attributesForUpdate[productId];
            this._addValuesToHtmlElements(content);
        },

        /**
         * Update simple product attribute values asynchronously
         * @param productId
         * @private
         */
        _updateAttributeValuesAsynchronously: function (productId) {
            let response = getAsyncValues(productId),
                widget = this;

            response
                .then(data => data.json())
                .then(result => {
                    if (result.success) {
                        widget._addValuesToHtmlElements(result.data);
                    }
                });
        },

        /**
         * Add values to the html elements
         * @param content
         * @private
         */
        _addValuesToHtmlElements: function (content) {
            $.each(content, function (index, item) {
                if ($(item.selector).length) {
                    $(item.selector).html(item.value);
                }
            });
        }
    };

    return function (swatchRenderer) {
        $.widget('mage.SwatchRenderer', swatchRenderer, swatchRendererMixin);
        return $.mage.SwatchRenderer;
    }
});
