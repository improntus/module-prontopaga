<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Improntus\ProntoPaga\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface PaymentMethodsRepositoryInterface
{

    /**
     * Save payment_methods
     * @param \Improntus\ProntoPaga\Api\Data\PaymentMethodsInterface $paymentMethods
     * @return \Improntus\ProntoPaga\Api\Data\PaymentMethodsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Improntus\ProntoPaga\Api\Data\PaymentMethodsInterface $paymentMethods
    );

    /**
     * Retrieve payment_methods
     * @param string $entityId
     * @return \Improntus\ProntoPaga\Api\Data\PaymentMethodsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($entityId);

     /**
     * Retrieve payment_methods
     * @param string $method
     * @return \Improntus\ProntoPaga\Api\Data\PaymentMethodsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByMethod($method);

    /**
     * Retrieve payment_methods matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Improntus\ProntoPaga\Api\Data\PaymentMethodsSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete payment_methods
     * @param \Improntus\ProntoPaga\Api\Data\PaymentMethodsInterface $paymentMethods
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Improntus\ProntoPaga\Api\Data\PaymentMethodsInterface $paymentMethods
    );

    /**
     * Delete payment_methods by ID
     * @param string $paymentMethodsId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($paymentMethodsId);
}

