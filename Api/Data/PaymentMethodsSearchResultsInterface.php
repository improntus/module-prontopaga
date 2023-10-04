<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Improntus\ProntoPaga\Api\Data;

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
