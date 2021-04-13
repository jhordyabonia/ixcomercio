<?php
namespace Intcomex\Catalog\Ui\DataProvider\Product\Form\Modifier;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
/**
 * Class DisableFields
 * @package Intcomex\Catalog\Ui\DataProvider\Product\Form\Modifier
 */
class DisableFields extends AbstractModifier
{
    /**
     * @var ArrayManager
     */
    protected $arrayManager;
    /**
     * DisableFields constructor
     *
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }
    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $meta = $this->disableFields($meta);
        return $meta;
    }
    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }
    /**
     * @param array $meta
     * @return array
     */
    protected function disableFields(array $meta)
    {
        $fields = [
            'special_price',
            'cost'
        ];
        foreach ($fields as $field) {
            $weightPath = $this->arrayManager->findPath($field, $meta, null, 'children');
            if ($weightPath) {
                $meta = $this->arrayManager->merge(
                    $weightPath . static::META_CONFIG_PATH,
                    $meta,
                    [
                        'disabled' => true
                    ]
                );
            }
        }
        return $meta;
    }
}
