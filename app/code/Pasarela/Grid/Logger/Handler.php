<?php
/**
 * Grid Logger Handler.
 * @category  Pasarela
 * @package   Pasarela_Grid
 * @author    Valentina Aguirre
 * @copyright Copyright (c) 2010-2017 Pasarela Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Pasarela\Grid\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level.
     *
     * @var int
     */
    public $loggerType = Logger::INFO;

    /**
     * File name.
     *
     * @var string
     */
    public $fileName = '/var/log/grid.log';
}
