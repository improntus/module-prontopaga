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
    const PAYMENT_SUCCESS = 'success';
    const PAYMENT_REJECTED = 'rejected';
    const PAYMENT_ERROR = 'error';

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

        if (!$result) {
            $message = (__('There was a problem retrieving data from Pronto Paga. Wait for status confirmation from Pronto Paga.'));
            $this->checkoutSession->setErrorMessage($message);
            $resultRedirect->setPath(self::FAILRURE_PATH);
            return $resultRedirect;
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->checkoutSession->getLastRealOrder();

        if ($this->prontoPagaHelper->isDebugEnabled()) {
            $path = $this->validatePayment($order);
        }else{
            if (isset($result['type']) && $type = $result['type'] ) {
                if ($type === ProntoPagaHelper::STATUS_REJECTED || $type === ProntoPagaHelper::STATUS_ERROR) {
                    $message = $this->checkoutSession->getProntoPagaError()
                        ?? (__('There was a problem retrieving data from Pronto Paga. Wait for status confirmation from Pronto Paga.'));
                    $this->checkoutSession->setErrorMessage($message);
                    $path = self::FAILRURE_PATH;
                }else if ($type === ProntoPagaHelper::STATUS_FINAL ||$type === ProntoPagaHelper::STATUS_CONFIRMATION ) {
                    $path = self::SUCCESS_PATH;
                }
            }
        }

        $resultRedirect->setPath($path);
        return $resultRedirect;
    }


    /**
     * Validate payment after redirect
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    private function validatePayment($order)
    {
        $transaction = $this->prontoPaga->transactionRepository->getByOrderId($order->getId());
        $response = $this->prontoPaga->webService->confirmPayment($transaction->getTransactionId());
        if (in_array($response['code'], ProntoPagaHelper::STATUS_OK)) {
            $unserializeResponse = $this->prontoPaga->json->unserialize($response['body']['message']);

            if ($unserializeResponse['status'] === self::PAYMENT_REJECTED || $unserializeResponse['status'] === self::PAYMENT_ERROR) {
                $path = $this->rejectedPayment($order, $unserializeResponse['status']);
            }else if ($unserializeResponse['status'] === self::PAYMENT_SUCCESS) {
                $this->approvedPayment($order);
                $path = $this->confirmationPayment($order, $unserializeResponse['uid']);
            }

            $this->prontoPaga->persistTransaction($order, $response, $unserializeResponse['status']);
            $this->prontoPaga->invoice($order, $transaction->getTransactionId());
        }
        return $path ?? self::FAILRURE_PATH;
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
        $this->prontoPaga->addSuccessToStatusHistory($order);
        return self::SUCCESS_PATH;
    }

    /**
     *
     * @param \Magento\Sales\Model\Order $order
     * @param string $type
     * @return void
     */
    private function rejectedPayment($order)
    {
        $message = $this->checkoutSession->getProntoPagaError() ?? (__('There was a problem retrieving data from Pronto Paga. Wait for status confirmation from Pronto Paga.'));
        $this->prontoPaga->cancelOrder($order, $message);
        $this->checkoutSession->setErrorMessage($message);
        return self::FAILRURE_PATH;
    }
}
