<?php
declare(strict_types=1);

namespace Improntus\ProntoPaga\Api\Data;

/**
 * @author Improntus <http://www.improntus.com> - Adobe Gold Partner - Elevating digital experience
 * @copyright Copyright (c) 2025 Improntus
 */
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
