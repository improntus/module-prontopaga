<?php
declare(strict_types=1);

namespace Improntus\ProntoPaga\Api\Data;

/**
 * @author Improntus <http://www.improntus.com> - Adobe Gold Partner - Elevating digital experience
 * @copyright Copyright (c) 2025 Improntus
 */
interface PaymentMethodsSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get payment_methods list.
     * @return \Improntus\ProntoPaga\Api\Data\PaymentMethodsInterface[]
     */
    public function getItems();

    /**
     * Set name list.
     * @param \Improntus\ProntoPaga\Api\Data\PaymentMethodsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
