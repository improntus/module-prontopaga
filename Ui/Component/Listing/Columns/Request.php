<?php
/*
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Improntus\ProntoPaga\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Request extends Column
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
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface       $urlBuilder,
        array              $components = [],
        array              $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getName()] = '<code style="
                max-width: 320px;
                display: block;
                max-height: 200px;
                overflow: auto;
                font-family: Consolas, courier new;
                color: #026062;
                background-color: #f1f1f1;
                padding: 5px;
                font-size: 105%;
                ">' . $item[$this->getName()] . '</code>';
            }
        }

        return $dataSource;
    }
}
