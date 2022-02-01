<?php

namespace Intcomex\Catalog\Helper;

use Magento\Framework\App\ScopeInterface;

class Timezone extends \Magento\Framework\Stdlib\DateTime\Timezone
{
    /**
     * @inheritdoc
     */
    public function isScopeDateInInterval($scope, $dateFrom = null, $dateTo = null): bool
    {
        if (!$scope instanceof ScopeInterface) {
            $scope = $this->_scopeResolver->getScope($scope);
        }

        $scopeTimeStamp = $this->scopeTimeStamp($scope);
        $fromTimeStamp = strtotime($dateFrom);
        $toTimeStamp = strtotime($dateTo);

        $result = false;
        if (!$this->_dateTime->isEmptyDate($dateFrom) && $scopeTimeStamp < $fromTimeStamp) {
        } elseif (!$this->_dateTime->isEmptyDate($dateTo) && $scopeTimeStamp > $toTimeStamp) {
        } else {
            $result = true;
        }

        return $result;
    }
}
