<?php
/*
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Improntus\ProntoPaga\Ui\Component\Listing\Columns;

use Magento\Framework\Data\OptionSourceInterface;
use Improntus\ProntoPaga\Model\ResourceModel\PaymentMethods\CollectionFactory;

class PaymentMehods implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * @var CollectionFactory
     */
    private $paymentMethods;

    /**
     * Constructor
     *
     * @param CollectionFactory $paymentMethods
     */
    public function __construct(
        CollectionFactory $paymentMethods
    ) {
        $this->paymentMethods = $paymentMethods;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->_options === null) {
            $methods = $this->getMethods();
            foreach ($methods as $method) {
                $this->_options[] = [
                    'value' => $method->getMethod(),
                    'label' => $method->getName()
                ];
            }
        }
        return $this->_options;
    }

    /**
     * @return \Improntus\ProntoPaga\Model\PaymentMethods
     */
    public function getMethods()
    {
        $collection = $this->paymentMethods->create();
        $collection->addFieldToSelect('*');
        return $collection->getItems();
    }
}
