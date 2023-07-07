<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Improntus\ProntoPaga\Api;

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
