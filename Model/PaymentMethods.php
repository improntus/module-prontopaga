<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Improntus\ProntoPaga\Model;

use Improntus\ProntoPaga\Api\Data\PaymentMethodsInterface;
use Magento\Framework\Model\AbstractModel;

class PaymentMethods extends AbstractModel implements PaymentMethodsInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Improntus\ProntoPaga\Model\ResourceModel\PaymentMethods::class);
    }

    /**
     * @inheritDoc
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getMethod()
    {
        return $this->getData(self::METHOD);
    }

    /**
     * @inheritDoc
     */
    public function setMethod($method)
    {
        return $this->setData(self::METHOD, $method);
    }

    /**
     * @inheritDoc
     */
    public function getCurrency()
    {
        return $this->getData(self::CURRENCY);
    }

    /**
     * @inheritDoc
     */
    public function setCurrency($currency)
    {
        return $this->setData(self::CURRENCY, $currency);
    }

    /**
     * @inheritDoc
     */
    public function getLogo()
    {
        return $this->getData(self::LOGO);
    }

    /**
     * @inheritDoc
     */
    public function setLogo($logo)
    {
        return $this->setData(self::LOGO, $logo);
    }
}

