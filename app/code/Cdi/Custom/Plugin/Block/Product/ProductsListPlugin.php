<?php    
namespace Cdi\Custom\Plugin\Block\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogWidget\Block\Product\ProductsList;

/**
 * Class ProductsListPlugin
 */
class ProductsListPlugin
{

    /**
     * @param ProductsList $subject
     * @param Collection $result
     * @return Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateCollection(ProductsList $subject, Collection $result)
    {
        $result->getSelect()->order('is_salable desc');

        return $result;
    }
}