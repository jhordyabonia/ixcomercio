<?php    
namespace Cdi\Custom\Plugin\Block\Product;

/**
 * Class ProductsListPlugin
 */
class ProductsListPlugin 
{
    public function afterCreateCollection($subject, $result)
    {
        $result->getSelect()->reset(\Zend_Db_Select::ORDER);
        $result->addAttributeToSort('is_saleable', 'desc');

        return $result;
    }

}

