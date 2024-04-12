<?php

/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Improntus\ProntoPaga\Controller\Order;

use Magento\Framework\App\ActionInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\RequestInterface;
use Improntus\ProntoPaga\Helper\Data as ProntoPagaHelper;
use Magento\Framework\Controller\Result\RedirectFactory;
use Improntus\ProntoPaga\Model\Payment\Prontopaga;
use Magento\Framework\Exception\LocalizedException;

class Response implements ActionInterface
{
    const FAILRURE_PATH = 'checkout/onepage/failure';
    const SUCCESS_PATH  = 'checkout/onepage/success';
    const ERROR_MESSAGE = 'There was a problem retrieving data from Pronto Paga. Wait for status confirmation from Pronto Paga.';

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ProntoPagaHelper
     */
    private $prontoPagaHelper;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var Prontopaga
     */
    private $prontoPaga;

    /**
     * Constructor
     *
     * @param CheckoutSession $checkoutSession
     * @param RequestInterface $request
     * @param ProntoPagaHelper $prontoPagaHelper
     * @param RedirectFactory $redirectFactory
     * @param Prontopaga $prontoPaga
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        RequestInterface $request,
        ProntoPagaHelper $prontoPagaHelper,
        RedirectFactory $redirectFactory,
        Prontopaga $prontoPaga
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->request = $request;
        $this->prontoPagaHelper = $prontoPagaHelper;
        $this->redirectFactory = $redirectFactory;
        $this->prontoPaga = $prontoPaga;
    }


    /**
     * @throws LocalizedException
     */
    public function execute()
    {
        $resultRedirect = $this->redirectFactory->create();
        $result = $this->request->getParams();
        $this->prontoPagaHelper->log(['type' => 'info', 'message' => $this->prontoPaga->json->serialize($result), 'method' => __METHOD__]);

        if (!$result) {
            $this->checkoutSession->setErrorMessage(self::ERROR_MESSAGE);
            $resultRedirect->setPath(self::FAILRURE_PATH);
            return $resultRedirect;
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->checkoutSession->getLastRealOrder();

        if ($this->prontoPagaHelper->validateOnCheckout()) {
            $path = $this->validatePayment($order);
            $resultRedirect->setPath($path);
            return $resultRedirect;
        }

        if ($type = $this->validatePayment($order, true)) {
            if (in_array($type, ProntoPagaHelper::STATUSES_CANCEL)) {
                $path = $this->rejectedPayment($order);
            } elseif (in_array($type, ProntoPagaHelper::STATUSES_SUCCESS)) {
                $path = self::SUCCESS_PATH;
            }
        }

        $resultRedirect->setPath($path ?? self::FAILRURE_PATH);
        return $resultRedirect;
    }

    /**
     *
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    private function confirmationPayment($order, $transactionId = null)
    {
        $this->prontoPaga->invoice($order, $transactionId);
        return self::SUCCESS_PATH;
    }

    /**
     *
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    private function approvedPayment($order)
    {
        $this->prontoPaga->addSuccessToStatusHistory($order, ProntoPagaHelper::ORIGIN_CHECKOUT);
        return self::SUCCESS_PATH;
    }

    /**
     *
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Framework\Phrase|string $type
     * @return void
     */
    private function rejectedPayment($order)
    {
        $message = $this->checkoutSession->getProntoPagaError()
            ?? (__('There was a problem retrieving data from Pronto Paga. Wait for status confirmation from Pronto Paga.'));
        $this->prontoPaga->cancelOrder($order, $message);
        $this->checkoutSession->setProntoPagaError('');
        $this->checkoutSession->setErrorMessage($message ?? '');
        return self::FAILRURE_PATH;
    }

    /**
     * Validate payment after redirect
     *
     * @param \Magento\Sales\Model\Order $order
     * @param bool $onlyStatus
     * @return string|bool
     */
    private function validatePayment($order, $onlyStatus = false)
    {
        $transaction = $this->prontoPaga->transactionRepository->getByOrderId($order->getId());
        $response = $this->prontoPaga->webService->confirmPayment($transaction->getTransactionId());
        if (in_array($response['code'], ProntoPagaHelper::STATUS_OK)) {
            $decodeResponse = $response['body'] ?? '';
            $status = $decodeResponse['status'];

            if ($onlyStatus) {
                return $status;
            }

            if (in_array($status, ProntoPagaHelper::STATUSES_CANCEL)) {
                $path = $this->rejectedPayment($order, $status);
            } elseif ($status === ProntoPagaHelper::STATUS_SUCCESS) {
                $this->approvedPayment($order);
                $path = $this->confirmationPayment($order, $decodeResponse['uid']);
                $this->prontoPaga->invoice($order, $transaction->getTransactionId());
            }

            $this->prontoPaga->persistTransaction($order, $response, $status);
        }
        return $path ?? self::FAILRURE_PATH;
    }
}
