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

class RowAction extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = [
                    // 'edit' => [
                    //     'href' =>
                    //         $this->urlBuilder->getUrl(
                    //             'improntus_prontopaga/transaction/detail',
                    //             ['entity_id' => $item['entity_id']]
                    //         ),
                    //     'label' => __('View'),
                    //     'hidden' => false
                    // ],
                    'view' => [
                        'href' =>
                            $this->urlBuilder->getUrl(
                                'improntus_prontopaga/transaction/detail',
                                ['entity_id' => $item['entity_id']]
                            ),
                        'label' => __('View'),
                        'hidden' => false
                    ],
                ];
            }
        }

        return $dataSource;
    }
}
