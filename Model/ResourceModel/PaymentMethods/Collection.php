<?php
declare(strict_types=1);

namespace Improntus\ProntoPaga\Model\ResourceModel\PaymentMethods;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @author Improntus <http://www.improntus.com> - Adobe Gold Partner - Elevating digital experience
 * @copyright Copyright (c) 2025 Improntus
 */
class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Improntus\ProntoPaga\Model\PaymentMethods::class,
            \Improntus\ProntoPaga\Model\ResourceModel\PaymentMethods::class
        );
    }
}
