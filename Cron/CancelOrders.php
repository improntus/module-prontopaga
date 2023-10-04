<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Improntus\ProntoPaga\Cron;

use Improntus\ProntoPaga\Api\TransactionRepositoryInterface;
use Improntus\ProntoPaga\Helper\Data as ProntoPagaHelper;
use Improntus\ProntoPaga\Model\Payment\Prontopaga as ProntoPaga;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\Order;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class CancelOrders
{
    const PENDING = 'pending';
    const PAYMENT_METHOD = 'prontopaga';
    const TRANSACTION_CANCELED = 'canceled';

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var ProntoPagaHelper
     */
    private $prontoPagaHelper;

    /**
     * @var ProntoPaga
     */
    private $prontoPaga;

    /**
     * @var OrderCollection
     */
    private $orderCollection;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * Constructor
     *
     * @param TransactionRepositoryInterface $transactionRepository
     * @param ProntoPagaHelper $prontoPagaHelper
     * @param ProntoPaga $prontoPaga
     * @param OrderCollection $orderCollection
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        ProntoPagaHelper $prontoPagaHelper,
        ProntoPaga $prontoPaga,
        OrderCollection $orderCollection,
        TimezoneInterface $timezone
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->prontoPagaHelper = $prontoPagaHelper;
        $this->prontoPaga = $prontoPaga;
        $this->orderCollection = $orderCollection;
        $this->timezone = $timezone;
    }

     /**
      * @return void
      */
    public function execute()
    {
        if (!$this->prontoPagaHelper->isCronEnabled()) {
            return;
        }

        $collection = $this->getOrderCollection(self::PENDING, $this->getCreatedAt());
        foreach ($collection as $order) {
            if ($order->getState() !== Order::STATE_NEW ||
                $order->getStatus() === Order::STATE_PAYMENT_REVIEW) {
                continue;
            }

            $message = (__("Order canceled by cron after {$this->prontoPagaHelper->getTimeInterval()} minutes pending."));
            $this->prontoPaga->cancelOrder($order, $message);
            $this->updateTransactionGrid($order->getEntityId());
        }
    }

    /**
     * @param $status
     * @return OrderCollection
     */
    private function getOrderCollection($status, $createdAt)
    {
        $this->orderCollection->getSelect()
            ->joinLeft(
                ["sop" => "sales_order_payment"],
                'main_table.entity_id = sop.parent_id',
                ['method']
            )->where('sop.method = ?', self::PAYMENT_METHOD)
            ->where('sop.last_trans_id IS NULL');
        $this->orderCollection->addFieldToFilter('main_table.status', $status)
                ->addFieldToFilter('main_table.created_at', ['lteq' => $createdAt])
                ->setOrder('main_table.created_at', 'ASC');
        return $this->orderCollection;
    }

     /**
      * Retrieve formatted locale date
      *
      * @return string
      */
    public function getCreatedAt(): string
    {
        $timeInterval = $this->prontoPagaHelper->getTimeInterval();
        $prevDate = date_create(date('Y-m-d H:i:s', strtotime("-{$timeInterval} min")));
        // return $this->timezone->date($prevDate)->format('Y-m-d H:i:s');
        return $prevDate->format('Y-m-d H:i:s');
    }

    /**
     * Update transaction records
     *
     * @param string|int|float $orderId
     * @return void
     */
    private function updateTransactionGrid($orderId)
    {
        $transaction = $this->transactionRepository->getByOrderId($orderId);
        if ($transaction) {
            $transaction->setStatus(self::TRANSACTION_CANCELED)->save();
        }
    }
}
