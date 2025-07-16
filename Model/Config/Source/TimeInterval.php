<?php
namespace Improntus\ProntoPaga\Model\Config\Source;

/**
 * @author Improntus <http://www.improntus.com> - Adobe Gold Partner - Elevating digital experience
 * @copyright Copyright (c) 2025 Improntus
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
        foreach (range(0, 100, 5) as $value) {
            $this->options[] = ['value' => $value, 'label' => $value];
        }
        return $this->options;
    }
}
