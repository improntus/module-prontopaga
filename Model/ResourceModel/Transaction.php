<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Improntus\ProntoPaga\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Transaction extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('improntus_prontopaga_transaction', 'entity_id');
    }
}
