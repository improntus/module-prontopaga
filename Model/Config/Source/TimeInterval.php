<?php
/*
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Improntus\ProntoPaga\Model\Config\Source;

/**
 * @api
 */
class TimeInterval implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $this->options = [['value' => '', 'label' => __('-- Please Select--')]];
        foreach(range(0, 100, 5) as $value){
            $this->options[] = ['value' => $value, 'label' => $value];
        }
        return $this->options;
    }
}
