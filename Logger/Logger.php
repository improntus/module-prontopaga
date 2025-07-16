<?php namespace Improntus\ProntoPaga\Logger;

/**
 * @author Improntus <http://www.improntus.com> - Adobe Gold Partner - Elevating digital experience
 * @copyright Copyright (c) 2025 Improntus
 */
class Logger extends \Monolog\Logger
{
    public function setName($name)
    {
        $this->name = $name;
    }
}
