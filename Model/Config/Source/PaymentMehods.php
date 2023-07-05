<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Improntus\ProntoPaga\Model\Config\Source;

use Improntus\ProntoPaga\Service\ProntoPagaApiService as WebService;
use Improntus\ProntoPaga\Helper\Data as ProntoPagaHelper;

class PaymentMehods implements \Magento\Framework\Option\ArrayInterface
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
     * Constructor
     *
     * @param WebService $webService
     * @param ProntoPagaHelper $prontoPagaHelper
     */
    public function __construct(
        WebService $webService,
        ProntoPagaHelper $prontoPagaHelper,
        array $options = []
    ) {
        $this->webService = $webService;
        $this->prontoPagaHelper = $prontoPagaHelper;
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
            }
        } catch (\Exception $e) {
            $this->prontoPagaHelper->log(['type' => 'info', 'message' => $response['methods']['message'] ?? 'No payment methods.', 'method' => __METHOD__]);
        }

        return $this->options ?: $this->setDefaultPayments();
    }

    private function setDefaultPayments()
    {
        return [
            ['value' => '', 'label' => __('No payment methods available.')],
        ];
    }
}
