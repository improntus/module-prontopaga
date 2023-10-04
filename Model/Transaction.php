<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Improntus\ProntoPaga\Model;

use Improntus\ProntoPaga\Api\Data\TransactionInterface;
use Magento\Framework\Model\AbstractModel;

class Transaction extends AbstractModel implements TransactionInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Improntus\ProntoPaga\Model\ResourceModel\Transaction::class);
    }

    /**
     * @inheritDoc
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * @inheritDoc
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getTransactionId()
    {
        return $this->getData(self::TRANSACTION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setTransactionId($transactionId)
    {
        return $this->setData(self::TRANSACTION_ID, $transactionId);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getRequestBody()
    {
        return $this->getData(self::REQUEST_BODY);
    }

    /**
     * @inheritDoc
     */
    public function setRequestBody($requestBody)
    {
        return $this->setData(self::REQUEST_BODY, $requestBody);
    }

    /**
     * @inheritDoc
     */
    public function getRequestResponse()
    {
        return $this->getData(self::REQUEST_RESPONSE);
    }

    /**
     * @inheritDoc
     */
    public function setRequestResponse($requestResponse)
    {
        return $this->setData(self::REQUEST_RESPONSE, $requestResponse);
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethod()
    {
        return $this->getData(self::PAYMENT_METHOD);
    }

    /**
     * @inheritDoc
     */
    public function setPaymentMethod($paymentMethod)
    {
        return $this->setData(self::PAYMENT_METHOD, $paymentMethod);
    }
}
