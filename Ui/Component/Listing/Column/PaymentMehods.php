<?php
/*
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Improntus\ProntoPaga\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Improntus\ProntoPaga\Api\PaymentMethodsRepositoryInterface as PaymentMethodsInterface;

class PaymentMehods extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var PaymentMethodsInterface
     */
    private $paymentMethodsInterface;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param PaymentMethodsInterface $paymentMethodsInterface
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        PaymentMethodsInterface $paymentMethodsInterface,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->paymentMethodsInterface = $paymentMethodsInterface;
    }

    /**
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = $this->getMethodName($item['payment_method']);
            }
        }

        return $dataSource;
    }

    /**
     *
     * @param string $method
     * @return string
     */
    private function getMethodName($method): string
    {
        $method = $this->paymentMethodsInterface->getByMethod($method);
        return $method ? $method->getName() : '';
    }
}
