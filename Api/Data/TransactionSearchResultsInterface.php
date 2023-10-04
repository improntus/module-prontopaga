<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Improntus\ProntoPaga\Api\Data;

interface TransactionSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get transaction list.
     * @return \Improntus\ProntoPaga\Api\Data\TransactionInterface[]
     */
    public function getItems();

    /**
     * Set order_id list.
     * @param \Improntus\ProntoPaga\Api\Data\TransactionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
