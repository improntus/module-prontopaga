<?php
namespace Improntus\ProntoPaga\Logger\Handler;

use Magento\Framework\Logger\Handler\Base as BaseHandler;
use Monolog\Logger as MonologLogger;

/**
 * @author Improntus <http://www.improntus.com> - Adobe Gold Partner - Elevating digital experience
 * @copyright Copyright (c) 2025 Improntus
 */
class CustomHandler extends BaseHandler
{
    protected $loggerType = MonologLogger::INFO;
    protected $fileName = 'var/log/prontopaga/info.log';
}
