<?php
declare(strict_types=1);

namespace Improntus\ProntoPaga\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * @author Improntus <http://www.improntus.com> - Adobe Gold Partner - Elevating digital experience
 * @copyright Copyright (c) 2025 Improntus
 */
class PaymentMethods extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('improntus_prontopaga_payment_methods', 'entity_id');
    }
}
