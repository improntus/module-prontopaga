<?php
/*
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Improntus\ProntoPaga\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Improntus\ProntoPaga\Helper\Data as ProntoPagaHelper;
use Magento\Framework\App\ResourceConnection;

class CreditmemoSaveBeforeObserver implements ObserverInterface
{

    /**
     * @var ProntoPagaHelper
     */
    private $prontoPagaHelper;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Constructor
     *
     * @param ProntoPagaHelper $prontoPagaHelper
     * @param ResourceConnection $resource
     */
    public function __construct(
        ProntoPagaHelper $prontoPagaHelper,
        ResourceConnection $resource
    ) {
        $this->prontoPagaHelper = $prontoPagaHelper;
        $this->resource = $resource;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        if (!$this->prontoPagaHelper->isEnabled() || !$this->prontoPagaHelper->isRefundEnabled()) {
            return $this;
        }

        /** @var Creditmemo $creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        /** @var Order $order */
        $order = $creditmemo->getOrder();

        if ($order->getPayment()->getMethodInstance()->getCode() !== ProntoPagaHelper::PAYMENT_CODE) {
            return $this;
        }

        $this->resource->getConnection()->beginTransaction();
        return $this;
    }
}
