<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Improntus\ProntoPaga\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Improntus\ProntoPaga\Api\TransactionRepositoryInterface;

class Detail extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var TransactionRepositoryInterface
     */
    private $_transactionInterface;

    /**
     * Imported constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param TransactionRepositoryInterface $transactionInterface
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        TransactionRepositoryInterface $transactionInterface,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->_transactionInterface = $transactionInterface;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $transaction  = $this->getDataIdByOrderId($item['entity_id']);
                $item[$this->getData('name')] = $transaction ? $this->getActionView($transaction) : $this->getAdvertising();
            }
        }
        return $dataSource;
    }

    /**
     *
     * @param string $orderId
     * @return void
     */
    public function getDataIdByOrderId(string $orderId)
    {
        return $this->_transactionInterface->getByOrderId($orderId);
    }

    /**
     * Get view ERP data action
     *
     * @param \Improntus\ProntoPaga\Model\Transaction $transaction
     * @return array
     */
    public function getActionView($transaction): array
    {
        return [
            'view' => [
                'href' => $this->urlBuilder->getUrl(
                    'improntus_prontopaga/transaction/detail',
                    [
                        'entity_id' => $transaction->getEntityId()
                    ]
                ),
                'label' => __('View Data'),
                'target' => '_blank'
            ]
        ];
    }


    /**
     * Get not imported ERP action
     *
     * @return array
     */
    public function getAdvertising(): array
    {
        return [
            'view' => [
                'href' => '#',
                'label' => __('N/A')
            ]
        ];
    }
}
