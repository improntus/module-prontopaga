<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Improntus\ProntoPaga\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface TransactionRepositoryInterface
{

    /**
     * Save transaction
     * @param \Improntus\ProntoPaga\Api\Data\TransactionInterface $transaction
     * @return \Improntus\ProntoPaga\Api\Data\TransactionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Improntus\ProntoPaga\Api\Data\TransactionInterface $transaction
    );

    /**
     * Retrieve transaction
     * @param string $entityId
     * @return \Improntus\ProntoPaga\Api\Data\TransactionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($entityId);

     /**
      * Retrieve transaction
      * @param string $transactionId
      * @return \Improntus\ProntoPaga\Api\Data\TransactionInterface
      * @throws \Magento\Framework\Exception\LocalizedException
      */
    public function getByTransactionId($transactionId);


    /**
     * Retrieve transaction by order id
     * @param string $orderId
     * @return \Improntus\ProntoPaga\Api\Data\TransactionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByOrderId($orderId);

    /**
     * Retrieve transaction matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Improntus\ProntoPaga\Api\Data\TransactionSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete transaction
     * @param \Improntus\ProntoPaga\Api\Data\TransactionInterface $transaction
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Improntus\ProntoPaga\Api\Data\TransactionInterface $transaction
    );

    /**
     * Delete transaction by ID
     * @param string $entityId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($entityId);
}
