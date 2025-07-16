<?php
namespace Improntus\ProntoPaga\Api;

/**
 * @author Improntus <http://www.improntus.com> - Adobe Gold Partner - Elevating digital experience
 * @copyright Copyright (c) 2025 Improntus
 */
use Magento\Framework\Webapi\Exception;

/**
 * @api
 */
interface CallbackInterface
{
    /**
     * @throws Exception
     * @return mixed
     */
    public function confirmOrder();
}
