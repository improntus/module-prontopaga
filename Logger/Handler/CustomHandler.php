<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Improntus\ProntoPaga\Logger\Handler;

use Magento\Framework\Logger\Handler\Base as BaseHandler;
use Monolog\Logger as MonologLogger;

class CustomHandler extends BaseHandler
{
    protected $loggerType = MonologLogger::INFO;
    protected $fileName = 'var/log/prontopaga/info.log';
}
