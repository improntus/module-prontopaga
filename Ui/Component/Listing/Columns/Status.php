<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Improntus\ProntoPaga\Ui\Component\Listing\Columns;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->_options === null) {

            $this->_options = [
                ['value' => '', 'label' => __('--Please Select--')],
                ['value' => 'create', 'label' => __('Create')],
                ['value' => 'success', 'label' => __('Success')],
                ['value' => 'rejected', 'label' => __('Rejected')],
                ['value' => 'error', 'label' => __('Error')]
            ];
        }
        return $this->_options;
    }
}
