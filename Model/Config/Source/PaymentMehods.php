<?php

namespace Improntus\ProntoPaga\Model\Config\Source;

use Improntus\ProntoPaga\Service\ProntoPagaApiService as WebService;
use Improntus\ProntoPaga\Helper\Data as ProntoPagaHelper;
use Improntus\ProntoPaga\Model\PaymentMethods as PaymentMethods;
use Improntus\ProntoPaga\Api\PaymentMethodsRepositoryInterface as PaymentMethodsInterface;

/**
 * @author Improntus <http://www.improntus.com> - Adobe Gold Partner - Elevating digital experience
 * @copyright Copyright (c) 2025 Improntus
 */
class PaymentMehods implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var WebService
     */
    private $webService;

    /**
     * @var ProntoPagaHelper
     */
    private $prontoPagaHelper;

    /**
     * @var array
     */
    private $options;

    /**
     * @var PaymentMethods
     */
    private $paymentMethods;

    /**
     * @var PaymentMethodsInterface
     */
    protected $paymentMethodsInterface;

    /**
     * Constructor
     *
     * @param WebService $webService
     * @param ProntoPagaHelper $prontoPagaHelper
     * @param PaymentMethods $paymentMethods
     * @param PaymentMethodsInterface $paymentMethodsInterface
     */
    public function __construct(
        WebService $webService,
        ProntoPagaHelper $prontoPagaHelper,
        PaymentMethods $paymentMethods,
        PaymentMethodsInterface $paymentMethodsInterface,
        array $options = []
    ) {
        $this->webService = $webService;
        $this->prontoPagaHelper = $prontoPagaHelper;
        $this->paymentMethods = $paymentMethods;
        $this->paymentMethodsInterface = $paymentMethodsInterface;
        $this->options = $options;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        try {
            $response = $this->webService->getPaymentMethods(
                $this->prontoPagaHelper->getCurrency()
            );

            if (in_array($response['code'], ProntoPagaHelper::STATUS_OK)) {
                foreach ($response['methods'] as $value) {
                    $this->options[] = ['value' => $value['method'], 'label' => __($value['name'])];
                }
                $this->setPaymentsMethods($response);
            }
        } catch (\Exception $e) {
            $this->prontoPagaHelper->log(['type' => 'info', 'message' => $response['methods']['message'] ?? 'No payment methods.', 'method' => __METHOD__]);
        }

        return $this->options ?: $this->setDefaultPayments();
    }

    /**
     *
     * @return array
     */
    private function setDefaultPayments(): array
    {
        return [
            ['value' => '', 'label' => __('No payment methods available.')],
        ];
    }

    /**
     * @param array $request
     * @return void
     */
    private function setPaymentsMethods($request): void
    {
        foreach ($request['methods'] as $method) {
            /** @var \Improntus\ProntoPaga\Model\PaymentMethods $paymentMethod */
            $paymentMethod = $this->paymentMethodsInterface->getByMethod($method['method']);
            if ($paymentMethod) {
                $method['entity_id'] = $paymentMethod->getEntityId();
                $paymentMethod->setData($method)->save();
            } else {
                $this->paymentMethods->setData($method);
                $this->paymentMethodsInterface->save($this->paymentMethods);
            }
        }
    }
}
