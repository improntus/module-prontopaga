<?php
namespace Improntus\ProntoPaga\Model\Config\Source;

/**
 * @author Improntus <http://www.improntus.com> - Adobe Gold Partner - Elevating digital experience
 * @copyright Copyright (c) 2025 Improntus
 */
class Frequency implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected static $_options;

    const CRON_FRECUENTLY = 'F';

    const CRON_DAILY = 'D';

    const CRON_WEEKLY = 'W';

    const CRON_MONTHLY = 'M';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!self::$_options) {
            self::$_options = [
                ['label' => __('Frecuently'), 'value' => self::CRON_FRECUENTLY],
                ['label' => __('Daily'), 'value' => self::CRON_DAILY],
                ['label' => __('Weekly'), 'value' => self::CRON_WEEKLY],
                ['label' => __('Monthly'), 'value' => self::CRON_MONTHLY],
            ];
        }
        return self::$_options;
    }
}
