<?php
/*
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Improntus\ProntoPaga\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Improntus\ProntoPaga\Helper\Data as ProntoPagaHelper;
use Improntus\ProntoPaga\Service\ProntoPagaApiService as WebService;
use Improntus\ProntoPaga\Api\TransactionRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\SerializerInterface;
use Improntus\ProntoPaga\Model\Payment\Prontopaga as ProntoPagaPayment;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order;

class CreditmemoSaveAfterObserver implements ObserverInterface
{
    /**
     * @var ProntoPagaHelper
     */
    private $prontoPagaHelper;

    /**
     * @var WebService
     */
    private $webService;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionInterface;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var SerializerInterface
     */
    private $json;

    /**
     * Constructor
     *
     * @param ProntoPagaHelper $prontoPagaHelper
     * @param WebService $websService
     * @param TransactionRepositoryInterface $transactionInterface
     * @param ResourceConnection $resource
     * @param SerializerInterface $json
     */
    public function __construct(
        ProntoPagaHelper $prontoPagaHelper,
        WebService $websService,
        TransactionRepositoryInterface $transactionInterface,
        OrderRepositoryInterface $orderRepository,
        ResourceConnection $resource,
        SerializerInterface $json
    ) {
        $this->prontoPagaHelper = $prontoPagaHelper;
        $this->webService = $websService;
        $this->transactionInterface = $transactionInterface;
        $this->orderRepository = $orderRepository;
        $this->resource = $resource;
        $this->json = $json;
    }

    /**
     * Refund process.
     *
     * @param Observer $observer
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
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

        try {
            $transaction = $this->transactionInterface->getByOrderId($order->getEntityId());
            $requestData = $this->getRequestData($creditmemo, $order, $transaction);
            $requestData['sign'] = $this->prontoPagaHelper->firmSecretKey($requestData);
            $response = $this->webService->createRefund($requestData);
            if (in_array($response['code'], ProntoPagaHelper::STATUS_OK)) {
                if ($response['body']['status'] === ProntoPagaHelper::STATUS_SUCCESS) {
                    $this->updateTransaction($transaction);
                    $message = __('Refund confirmed by Pronto Paga, The credit memo was successfully refunded. Reference: %1', $creditmemo->getIncrementId());
                    $order->addCommentToStatusHistory($message);
                    $this->orderRepository->save($order);
                }
            } else {
                $this->prontoPagaHelper->log(['type' => 'error', 'message' => 'The credit memo couldn\'t be saved.', 'method' => __METHOD__]);
                throw new \Magento\Framework\Exception\LocalizedException(__('The credit memo couldn\'t be saved.'));
            }
        } catch (\Exception $e) {
            $this->prontoPagaHelper->log(['type' => 'error', 'message' => $e->getMessage(), 'method' => __METHOD__]);
            $this->resource->getConnection()->rollBack();
            throw new \Exception($e->getMessage());
        }

        $this->resource->getConnection()->commit();
        return $this;
    }

    /**
     * Get request data
     *
     * @param Creditmemo $creditmemo
     * @param Order $order
     * @param \Improntus\ProntoPaga\Api\Data\TransactionInterface $transaction
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getRequestData($creditmemo, $order, $transaction)
    {
        return [
            'reference' => $this->getReferenceId($transaction),
            'clientDocument' => $order->getPayment()->getAdditionalInformation(ProntoPagaPayment::DOCUMENT_KEY) ?? $order->getCustomerTaxvat() ?? '',
            'amount' => $creditmemo->getGrandTotal(),
            'urlCallbackRefund' => $this->prontoPagaHelper->getCallBackUrl(ProntoPagaHelper::STEP_REFUND)
        ];
    }

    /**
     * Retrieve transaction by order id
     *
     * @param \Improntus\ProntoPaga\Api\Data\TransactionInterface $transaction
     * @return string|null
     */
    private function getReferenceId($transaction): ?string
    {
        $requestResponse = $transaction->getRequestResponse();
        $requestResponse = $this->json->unserialize($requestResponse);
        return $requestResponse['reference'];
    }

    /**
     * Update transaction
     *
     * @param \Improntus\ProntoPaga\Api\Data\TransactionInterface $transaction
     * @return void
     */
    private function updateTransaction($transaction)
    {
        if ($this->prontoPagaHelper->localValidation()) {
            $transaction->setStatus(ProntoPagaHelper::STATUS_REFUNDED);
            $this->transactionInterface->save($transaction);
        }
    }
}
