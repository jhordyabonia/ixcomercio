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
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'underscore',
    'jquery/ui',
    'jquery/jquery.parsequery'
], function ($, _) {
    'use strict';
    return function (widget) {


        $.widget('bss.SwatchRenderer', widget, {
            options: {
                delay: 200,                             //how much ms before tooltip to show
                tooltipClass: 'swatch-option-tooltip'  //configurable, but remember about css
            },


            /**
             * Event for swatch options
             *
             * @param {Object} $this
             * @param {Object} $widget
             * @private
             */
            _OnClick: function ($this, $widget) {
                var options = $widget.options;
                if (!$widget.inProductList) {
                    var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                        $wrapper = $this.parents('.' + $widget.options.classes.attributeOptionsWrapper),
                        $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                        attributeId = $widget.getDataofAttr($parent, 'attribute-id'),
                        $input = $parent.find('.' + $widget.options.classes.attributeInput),
                        checkAdditionalData = JSON.parse(this.options.jsonSwatchConfig[attributeId]['additional_data']);

                    if ($widget.inProductList) {
                        $input = $widget.productForm.find(
                            '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                        );
                    }

                    if ($this.hasClass('disabled')) {
                        return;
                    }

                    if ($this.hasClass('selected')) {
                        $parent.removeAttr('data-option-selected option-selected').find('.selected').removeClass('selected');
                        $input.val('');
                        $label.text('');
                        $this.attr('aria-checked', false);
                    } else {
                        var optionId = $widget.getDataofAttr($this, 'option-id');
                        $parent.attr({ 'data-option-selected': optionId, 'option-selected': optionId }).find('.selected').removeClass('selected');
                        $label.text($widget.getDataofAttr($this, 'option-label'));
                        $input.val(optionId);
                        $input.attr('data-attr-name', this._getAttributeCodeById(attributeId));
                        $this.addClass('selected');
                        $widget._toggleCheckedAttributes($this, $wrapper);
                    }

                    $widget._Rebuild();

                    if ($widget.element.parents($widget.options.selectorProduct)
                        .find(this.options.selectorProductPrice).is(':data(mage-priceBox)')
                    ) {
                        $widget._UpdatePrice();
                    }

                    $(document).trigger('updateMsrpPriceBlock',
                        [
                            _.findKey($widget.options.jsonConfig.index, $widget.options.jsonConfig.defaultValues),
                            $widget.options.jsonConfig.optionPrices
                        ]);
                    if (!window.bssGallerySwitchStrategy || window.bssGallerySwitchStrategy != 'disabled') {
                        if (parseInt(checkAdditionalData['update_product_preview_image'], 10) === 1) {
                            $widget._loadMedia();
                        }
                    }

                    $input.trigger('change');
                }
                if ($widget.inProductList) {
                    $widget._super($this, $widget);
                    var childProductData = this.options.jsonConfig.bss_simple_detail;
                    if (!$.isEmptyObject(childProductData)
                        && childProductData && childProductData.child
                        && !$.isEmptyObject(childProductData.child)
                        && options.jsonConfig.is_enable_swatch_name
                    ) {
                        $widget._UpdateProductName($this);
                    }
                }
            },

            _EventListener: function () {
                var $widget = this,
                    options = this.options.classes,
                    target;
                $widget.element.on('click', '.' + options.optionClass, function (e) {
                    var classOn = this.className.split(" ");
                    var value = $widget.options.jsonConfig.bss_simple_detail.configvalues.size;
                    if (!classOn.find((str) => str == value)) {
                        $widget._OnClick($(this), $widget);
                        if (!$widget.inProductList) {
                            $widget._RenderValidateInputsSwathes($widget, "click");
                        }
                    } else {
                        return $widget._OnClick($(this), $widget);
                    }

                });

                $widget.element.on('change', '.' + options.selectClass, function () {
                    return $widget._OnChange($(this), $widget);
                });

                $widget.element.on('click', '.' + options.moreButton, function (e) {
                    e.preventDefault();

                    return $widget._OnMoreClick($(this));
                });

                $widget.element.on('keydown', function (e) {
                    if (e.which === 13) {
                        target = $(e.target);
                        if (target.is('.' + options.optionClass)) {
                            return $widget._OnClick(target, $widget);
                        } else if (target.is('.' + options.selectClass)) {
                            return $widget._OnChange(target, $widget);
                        } else if (target.is('.' + options.moreButton)) {
                            e.preventDefault();

                            return $widget._OnMoreClick(target);
                        }
                    }
                });
            },

            _RenderValidateInputsSwathes: function ($widget, type) {
                // preload size swathes crocs
                var $inputAttr = {};
                var attributesObj = {};
                $.each(this.options.jsonConfig.attributes, function (index, attribute) {
                    if (attribute.code == $widget.options.jsonConfig.bss_simple_detail.configvalues.color) {
                        attributesObj.color = attribute.id;
                        $inputAttr.color = $('[name="super_attribute[' + attribute.id + ']"]').val();
                    }
                    if (attribute.code == $widget.options.jsonConfig.bss_simple_detail.configvalues.gender) {
                        attributesObj.gender = attribute.id;
                        $inputAttr.gender = $('[name="super_attribute[' + attribute.id + ']"]').val();
                    }
                    if (attribute.code == $widget.options.jsonConfig.bss_simple_detail.configvalues.size) {
                        attributesObj.size = attribute;
                    }
                });
                this._RenderControlsSize($inputAttr.color, $inputAttr.gender, attributesObj.size, type);
            },

            
            /**
             * 
             * Render controls Size
             *
             * @private
             */
            _RenderControlsSize: function (colorAttr, genderAttr, sizeAttr, type) {
                var $widget = this,
                    container = this.element,
                    classes = this.options.classes,
                    chooseText = this.options.jsonConfig.chooseText;
                $widget._ClearSizeContainer();
                $widget.optionsMap = {};
                var itemSizeFilter = {
                    code: sizeAttr.code,
                    id: sizeAttr.id,
                    label: sizeAttr.label,
                    options: {},
                    position: "0"
                };
                var count = 0;
                $.each(this.options.jsonConfig.bss_simple_detail.child, function (index, item) {
                    if (item.gender == genderAttr && item.color == colorAttr) {
                        itemSizeFilter.options[count] = {
                            id: item.size,
                            label: item.size_label,
                            products: item.id
                        }
                        count++;
                    }
                });
                var controlLabelId = 'option-label-' + sizeAttr.code + '-' + sizeAttr.id;
                var options = $widget._RenderSwatchOptions(itemSizeFilter, controlLabelId, $widget);
                var select = $widget._RenderSwatchSelect(itemSizeFilter, chooseText);
                var input = $widget._RenderFormInput(itemSizeFilter);
                var listLabel = '';
                var label = '';

                if ($widget.options.onlySwatches && !$widget.options.jsonSwatchConfig.hasOwnProperty(sizeAttr.id)) {
                    return;
                }

                if ($widget.options.enableControlLabel) {
                    label +=
                        '<span id="' + controlLabelId + '" class="' + classes.attributeLabelClass + '">' +
                        $('<i></i>').text(sizeAttr.label).html() +
                        '</span>' +
                        '<span class="' + classes.attributeSelectedOptionLabelClass + '"></span>';
                }
                if ($widget.inProductList) {
                    $widget.productForm.append(input);
                    input = '';
                    listLabel = 'aria-label="' + $('<i></i>').text(sizeAttr.label).html() + '"';
                } else {
                    listLabel = 'aria-labelledby="' + controlLabelId + '"';
                }

                if (type == "init") {
                    container.append(
                        '<div class="' + classes.attributeClass + ' ' + sizeAttr.code + '" ' +
                        'attribute-code="' + sizeAttr.code + '" ' +
                        'attribute-id="' + sizeAttr.id + '">' +
                        label +
                        '<div aria-activedescendant="" ' +
                        'tabindex="0" ' +
                        'aria-invalid="false" ' +
                        'aria-required="true" ' +
                        'role="listbox" ' + listLabel +
                        'class="' + classes.attributeOptionsWrapper + ' clearfix">' +
                        options + select +
                        '</div>' + input +
                        '</div>'
                    );
                } else {
                    $('.swatch-attribute.crocs_size').append(
                        '<div aria-activedescendant="" ' +
                        'tabindex="0" ' +
                        'aria-invalid="false" ' +
                        'aria-required="true" ' +
                        'role="listbox" ' + listLabel +
                        'class="' + classes.attributeOptionsWrapper + ' clearfix">' +
                        options + select +
                        '</div>'
                    );
                }



            },

            _RenderSwatchOptions: function (config, controlId, $widget) {
                var optionConfig = this.options.jsonSwatchConfig[config.id],
                    optionClass = this.options.classes.optionClass,
                    sizeConfig = this.options.jsonSwatchImageSizeConfig,
                    moreLimit = parseInt(this.options.numberToShow, 10),
                    moreClass = this.options.classes.moreButton,
                    moreText = this.options.moreButtonText,
                    countAttributes = 0,
                    html = '';

                if (!this.options.jsonSwatchConfig.hasOwnProperty(config.id)) {
                    return '';
                }

                $.each(config.options, function (index) {
                    var id,
                        type,
                        value,
                        thumb,
                        label,
                        width,
                        height,
                        attr,
                        swatchImageWidth,
                        swatchImageHeight;

                    if (!optionConfig.hasOwnProperty(this.id)) {
                        return '';
                    }

                    // Add more button
                    if (moreLimit === countAttributes++) {
                        html += '<a href="#" class="' + moreClass + '"><span>' + moreText + '</span></a>';
                    }

                    id = this.id;
                    type = parseInt(optionConfig[id].type, 10);
                    value = optionConfig[id].hasOwnProperty('value') ?
                        $('<i></i>').text(optionConfig[id].value).html() : '';
                    thumb = optionConfig[id].hasOwnProperty('thumb') ? optionConfig[id].thumb : '';
                    width = _.has(sizeConfig, 'swatchThumb') ? sizeConfig.swatchThumb.width : 110;
                    height = _.has(sizeConfig, 'swatchThumb') ? sizeConfig.swatchThumb.height : 90;
                    label = this.label ? $('<i></i>').text(this.label).html() : '';
                    attr =
                        ' id="' + config.code + ' ' + controlId + '-item-' + id + '"' +
                        ' index="' + index + '"' +
                        ' aria-checked="false"' +
                        ' aria-describedby="' + controlId + '"' +
                        ' tabindex="0"' +
                        ' option-type="' + type + '"' +
                        ' option-id="' + id + '"' +
                        ' option-label="' + label + '"' +
                        ' aria-label="' + label + '"' +
                        ' option-tooltip-thumb="' + thumb + '"' +
                        ' option-tooltip-value="' + value + '"' +
                        ' role="option"' +
                        ' thumb-width="' + 120 + '"' +
                        ' thumb-height="' + height + '"';

                    swatchImageWidth = _.has(sizeConfig, 'swatchImage') ? sizeConfig.swatchImage.width : 30;
                    swatchImageHeight = _.has(sizeConfig, 'swatchImage') ? sizeConfig.swatchImage.height : 20;

                    if (!this.hasOwnProperty('products') || this.products.length <= 0) {
                        attr += ' option-empty="true"';
                    }

                    if (type === 0) {
                        // Text
                        html += '<div class="' + optionClass + ' ' + config.code + ' text" ' + attr + '>' + (value ? value : label) +
                            '</div>';
                    } else if (type === 1) {
                        // Color
                        html += '<div onclick="showPriceVariation(' + $widget.optionsMap[config.id][id]['price'] + ')"  class="' + optionClass + ' color" ' + attr +
                            ' style="background: ' + value +
                            ' no-repeat center; background-size: initial;">' + '' +
                            '</div>';
                    } else if (type === 2) {
                        // Image
                        html += '<div class="' + optionClass + ' image" ' + attr +
                            ' style="background: url(' + value + ') no-repeat center; background-size: initial;width:' +
                            swatchImageWidth + 'px; height:' + swatchImageHeight + 'px">' + '' +
                            '</div>';
                    } else if (type === 3) {
                        // Clear
                        html += '<div class="' + optionClass + '" ' + attr + '></div>';
                    } else {
                        // Default
                        html += '<div class="' + optionClass + '" ' + attr + '>' + label + '</div>';
                    }
                });

                return html;
            },

            /**
            * Render controls Size
            *
            * @private
            */
            _ClearSizeContainer: function () {
                $('.swatch-attribute.' + this.options.jsonConfig.bss_simple_detail.configvalues.size + ' div').remove();
            },

            /**
             * Update product name
             *
             * @param ele
             * @private
             */
            _UpdateProductName: function (ele) {
                var index = '',
                    childProductData = this.options.jsonConfig.bss_simple_detail,
                    $productName,
                    $widget = this;

                ele.parents(".product-item-details").find(".super-attribute-select").each(function () {
                    var option_id = $widget.getDataofAttr($(this), "option-selected");
                    if (typeof option_id === "undefined" && $(this).val() !== "") {
                        option_id = $(this).val();
                    }
                    if (option_id !== null && $(this).val() !== "") {
                        index += option_id + '_';
                    }
                });

                if (!childProductData['child'].hasOwnProperty(index)) {
                    this._ResetName(ele);
                    return false;
                }

                $productName = childProductData['child'][index]['name'];
                if ($productName) {
                    ele.parents(".product-item-details").find('.product-item-link').text($productName);
                }
            },

            /**
             * Reset default product name
             * @param ele
             * @private
             */
            _ResetName: function (ele) {
                var childProductData = this.options.jsonConfig.bss_simple_detail,
                    productName = childProductData['child']['default'];
                if (productName) {
                    ele.parents(".product-item-details").find('.product-item-link').text(productName);
                }
            },

            /**
             * Get attribute value,
             * Compatible with M2.3x and M2.4
             * Reason: Some important attributes were changed format (data-attribute in stead of attribute)
             *
             * @param element
             * @param name
             * @returns {*}
             */
            getDataofAttr(element, name) {
                var attr = element.attr(name);
                if (undefined !== attr && attr && attr.length) {
                    return attr;
                }
                return element.data(name);
            }
        });

        return $.bss.SwatchRenderer;
    }
});
