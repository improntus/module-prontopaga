<?php
/*
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Improntus\ProntoPaga\ViewModel\Onepage;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Improntus\ProntoPaga\Api\TransactionRepositoryInterface;
use Improntus\ProntoPaga\Helper\Data as ProntoPagaHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;

class FinalPage implements ArgumentInterface
{

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionInterface;

    /**
     * @var ProntoPagaHelper
     */
    private $prontoPagaHelper;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var PricingHelper
     */
    private $pricingHelper;

    /**
     * Constructor
     *
     * @param TransactionRepositoryInterface $transactionInterface
     * @param ProntoPagaHelper $prontoPagaHelper
     * @param CheckoutSession $checkoutSession
     * @param Json $json
     * @param PricingHelper $pricingHelper
     */
    public function __construct(
        TransactionRepositoryInterface $transactionInterface,
        ProntoPagaHelper $prontoPagaHelper,
        CheckoutSession $checkoutSession,
        Json $json,
        PricingHelper $pricingHelper
    ) {
        $this->transactionInterface = $transactionInterface;
        $this->prontoPagaHelper = $prontoPagaHelper;
        $this->checkoutSession = $checkoutSession;
        $this->json = $json;
        $this->pricingHelper = $pricingHelper;
    }

    /**
     * Retrieve payment info
     *
     * @return array
     */
    public function getPaymentInfo()
    {
        try {
            $transaction = $this->loadTransaction();
            $paymentInfo = $this->secureResponse($transaction->getRequestResponse());
        } catch (\Exception $e) {
            $this->prontoPagaHelper->log(['type' => 'warning', 'message' => $e->getMessage(), 'method' => __METHOD__]);
            return [];
        }
        return $paymentInfo ?: [];
    }

    /**
     *
     * @return boolean
     */
    public function validateOnCheckout(): bool
    {
        return $this->prontoPagaHelper->validateOnCheckout();
    }

    /**
     *
     * @param string|int|float $price
     * @return string
     */
    public function formatPrice($price): string
    {
        return $this->pricingHelper->currency($price, true, false);
    }

    /**
     * Retrieve transaction data
     *
     * @return \Improntus\ProntoPaga\Model\Transaction|boolean
     */
    private function loadTransaction()
    {
        $orderId = $this->checkoutSession->getLastRealOrder()->getEntityId();
        if (!$orderId) {
           return false;
        }

        return $this->transactionInterface->getByOrderId($orderId);
    }

    /**
     * Remove date from request response
     *
     * @param string $requestResponse
     * @return array
     */
    private function secureResponse($requestResponse): array
    {
        $requestResponse = $this->json->unserialize($requestResponse);
        if (!isset($requestResponse['hash'], $requestResponse['hash'])){
            return false;
        }
        unset($requestResponse['hash'], $requestResponse['sign']);
        return $requestResponse;
    }
}
