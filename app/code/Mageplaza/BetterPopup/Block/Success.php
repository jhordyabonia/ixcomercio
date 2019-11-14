<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_BetterPopup
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BetterPopup\Block;

/**
 * Class Success
 * @package Mageplaza\BetterPopup\Block
 */
class Success extends Popup
{
    /**
     * @var string
     */
    protected $_template = 'Mageplaza_BetterPopup::popup/success.phtml';

    /**
     * Get Coupon code
     *
     * @return array|mixed
     */
    public function getCouponCode()
    {
        return $this->_helperData->getWhatToShowConfig('popup_success/coupon_code');
    }

    /**
     * Get Html Popup success
     *
     * @return mixed
     */
    public function getPopupSuccessContent()
    {
        $htmlConfig = $this->_helperData->getWhatToShowConfig('popup_success/html_success_content');
		$search  = [
            '{{form_url}}',
            '{{url_loader}}',
            '{{email_icon_url}}',
            '{{bg_tmp2}}',
            '{{img_tmp3}}',
            '{{tmp3_icon_button}}',
            '{{bg_tmp4}}',
            '{{img_tmp4}}',
            '{{img_content_tmp5}}',
            '{{img_cap_tmp5}}',
            '{{img_email_tmp5}}',
            '{{img_tmp7}}',
            '{{tmp7_icon_button}}',
            '{{img_tmp8}}',
			'{{coupon_code}}'
        ];
        $replace = [
            $this->getFormActionUrl(),
            $this->getViewFileUrl('images/loader-1.gif'),
            $this->getViewFileUrl('Mageplaza_BetterPopup::images/mail-icon.png'),
            $this->getViewFileUrl('Mageplaza_BetterPopup::images/bg-tmp2.png'),
            $this->getViewFileUrl('Mageplaza_BetterPopup::images/template3/img-content.png'),
            $this->getViewFileUrl('Mageplaza_BetterPopup::images/template3/button-icon.png'),
            $this->getViewFileUrl('Mageplaza_BetterPopup::images/template4/bg.png'),
            $this->getViewFileUrl('Mageplaza_BetterPopup::images/template4/img-content.png'),
            $this->getViewFileUrl('Mageplaza_BetterPopup::images/template5/img-content.png'),
            $this->getViewFileUrl('Mageplaza_BetterPopup::images/template5/img-cap.png'),
            $this->getViewFileUrl('Mageplaza_BetterPopup::images/template5/img-email.png'),
            $this->getViewFileUrl('Mageplaza_BetterPopup::images/template7/img-content.jpg'),
            $this->getViewFileUrl('Mageplaza_BetterPopup::images/template7/icon_button.png'),
			$this->getViewFileUrl('Mageplaza_BetterPopup::images/template8/img-content.jpg'),
			$this->getCouponCode()
        ];

		$html = str_replace($search, $replace, $htmlConfig);
        //$html       = str_replace('{{coupon_code}}', $this->getCouponCode(), $htmlConfig);

        return $html;
    }
}