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
namespace Bss\XmlSiteMap\Observer\RobotsTxt;

use Bss\XmlSiteMap\Helper\Data as SiteMapHelper;
use Bss\XmlSiteMap\Model\Sitemap;
use Bss\XmlSiteMap\Model\ResourceModel\Sitemap\Collection as SiteMapCollection;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Escaper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManager;

/**
 * Class AddSiteMap
 *
 * @package Bss\XmlSiteMap\Observer\RobotsTxt
 */
class AddSiteMap implements ObserverInterface
{
    /**
     * @var SiteMapHelper
     */
    protected $siteMapHelper;

    /**
     * @var SiteMapCollection
     */
    protected $siteMapCollection;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * AddSiteMap constructor.
     *
     * @param SiteMapHelper $siteMapHelper
     * @param SiteMapCollection $siteMapCollection
     * @param Filesystem $filesystem
     * @param Escaper $escaper
     * @param StoreManager $storeManager
     */
    public function __construct(
        SiteMapHelper $siteMapHelper,
        SiteMapCollection $siteMapCollection,
        Filesystem $filesystem,
        Escaper $escaper,
        StoreManager $storeManager
    ) {
        $this->siteMapHelper = $siteMapHelper;
        $this->siteMapCollection = $siteMapCollection;
        $this->filesystem = $filesystem;
        $this->escaper = $escaper;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Observer $observer
     * @throws NoSuchEntityException
     * @throws ValidatorException
     */
    public function execute(Observer $observer)
    {
        $currentStoreId = $this->storeManager->getStore()->getId();
        if ($this->siteMapHelper->isEnableModule() &&
            $this->siteMapHelper->getEnableSubmissionRobots($currentStoreId)) {
            /** @var \Magento\Framework\DataObject $robotsTxtModel */
            $robotsTxtModel = $observer->getData('robots_txt');
            $robotsTxt = $robotsTxtModel->getData('robots_txt');
            $siteMapTxt = '';
            $siteMapCollection = $this->siteMapCollection->addFieldToFilter('store_id', ['eq' => $currentStoreId]);
            foreach ($siteMapCollection as $siteMapModel) {
                if ($this->getSiteMapUrl($siteMapModel)) {
                    $siteMapTxt .= 'Sitemap: ' . $this->getSiteMapUrl($siteMapModel) . PHP_EOL;
                }
            }
            // Remove the last PHP_EOL
            $siteMapTxt = trim($siteMapTxt);
            if ($siteMapTxt !== '' && strlen($siteMapTxt)) {
                $robotsTxtModel->setData('robots_txt', $robotsTxt . PHP_EOL . $siteMapTxt);
            }
        }
    }

    /**
     * @param Sitemap $siteMapModel
     * @return string|bool
     * @throws NoSuchEntityException
     * @throws ValidatorException
     */
    protected function getSiteMapUrl($siteMapModel)
    {
        $url = $this->escaper->escapeHtml($siteMapModel->getSiteMapUrlRaw());

        $fileName = preg_replace(
            '/^\//',
            '',
            $siteMapModel->getData('xml_sitemap_path') . $siteMapModel->getData('xml_sitemap_filename')
        );
        $rootPath = $this->siteMapHelper->getRootPath();
        $fileName = $rootPath . $fileName;
        $directory = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);

        if ($directory->isFile($fileName)) {
            return $url;
        }
        return false;
    }
}
