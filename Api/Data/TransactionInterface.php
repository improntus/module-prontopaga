<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Improntus\ProntoPaga\Api\Data;

interface TransactionInterface
{

    const ORDER_ID = 'order_id';
    const TRANSACTION_ID = 'transaction_id';
    const STATUS = 'status';
    const REQUEST_RESPONSE = 'request_response';
    const ENTITY_ID = 'entity_id';
    const CREATED_AT = 'created_at';
    const REQUEST_BODY = 'request_body';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId();

    /**
     * Set entity_id
     * @param string $entityId
     * @return \Improntus\ProntoPaga\Transaction\Api\Data\TransactionInterface
     */
    public function setEntityId($entityId);

    /**
     * Get order_id
     * @return string|null
     */
    public function getOrderId();

    /**
     * Set order_id
     * @param string $orderId
     * @return \Improntus\ProntoPaga\Transaction\Api\Data\TransactionInterface
     */
    public function setOrderId($orderId);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Improntus\ProntoPaga\Transaction\Api\Data\TransactionInterface
     */
    public function setStatus($status);

     /**
     * Get transaction_id
     * @return string|null
     */
    public function getTransactionId();

    /**
     * Set transaction_id
     * @param string $transactionId
     * @return \Improntus\ProntoPaga\Transaction\Api\Data\TransactionInterface
     */
    public function setTransactionId($transactionId);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Improntus\ProntoPaga\Transaction\Api\Data\TransactionInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get request_body
     * @return string|null
     */
    public function getRequestBody();

    /**
     * Set request_body
     * @param string $requestBody
     * @return \Improntus\ProntoPaga\Transaction\Api\Data\TransactionInterface
     */
    public function setRequestBody($requestBody);

    /**
     * Get request_response
     * @return string|null
     */
    public function getRequestResponse();

    /**
     * Set request_response
     * @param string $requestResponse
     * @return \Improntus\ProntoPaga\Transaction\Api\Data\TransactionInterface
     */
    public function setRequestResponse($requestResponse);
}

