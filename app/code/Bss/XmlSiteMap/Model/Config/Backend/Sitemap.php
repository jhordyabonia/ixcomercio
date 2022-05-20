<?php
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
 * @package    Bss_XmlSiteMap
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\XmlSiteMap\Model\Config\Backend;

/**
 * Class Sitemap
 *
 * @package Bss\XmlSiteMap\Model\Config\Backend
 */
class Sitemap extends \Magento\Cron\Model\Config\Backend\Sitemap
{
    /**
     * Cron string path
     */
    const CRON_STRING_PATH = 'crontab/default/jobs/bss_xmlsitemap_generate/schedule/cron_expr';

    /**
     * Cron mode path
     */
    const CRON_MODEL_PATH = 'crontab/default/jobs/bss_xmlsitemap_generate/run/model';

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Cron\Model\Config\Backend\Sitemap
     * @throws \Exception
     */
    public function afterSave()
    {
        $time = $this->getData('groups/generate/fields/time/value');
        $frequency = $this->getData('groups/generate/fields/frequency/value');

        $cronExprArray = [
            (int)$time[1], //Minute
            (int)$time[0], //Hour
            $frequency == \Magento\Cron\Model\Config\Source\Frequency::CRON_MONTHLY ? '1' : '*', //Day of the Month
            '*', //Month of the Year
            $frequency == \Magento\Cron\Model\Config\Source\Frequency::CRON_WEEKLY ? '1' : '*', //# Day of the Week
        ];

        $cronExprString = join(' ', $cronExprArray);

        try {
            $this->_configValueFactory->create()->load(
                self::CRON_STRING_PATH,
                'path'
            )->setValue(
                $cronExprString
            )->setPath(
                self::CRON_STRING_PATH
            )->save();
            $this->_configValueFactory->create()->load(
                self::CRON_MODEL_PATH,
                'path'
            )->setValue(
                $this->_runModelPath
            )->setPath(
                self::CRON_MODEL_PATH
            )->save();
        } catch (\Exception $e) {
            throw new \Exception(__('We can\'t save the cron expression.'));
        }
        return parent::afterSave();
    }
}