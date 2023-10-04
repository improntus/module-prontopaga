<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Improntus\ProntoPaga\Api\Data;

interface PaymentMethodsInterface
{

    const METHOD = 'method';
    const ENTITY_ID = 'entity_id';
    const NAME = 'name';
    const LOGO = 'logo';
    const CURRENCY = 'currency';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId();

    /**
     * Set entity_id
     * @param string $entityId
     * @return \Improntus\ProntoPaga\PaymentMethods\Api\Data\PaymentMethodsInterface
     */
    public function setEntityId($entityId);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \Improntus\ProntoPaga\PaymentMethods\Api\Data\PaymentMethodsInterface
     */
    public function setName($name);

    /**
     * Get method
     * @return string|null
     */
    public function getMethod();

    /**
     * Set method
     * @param string $method
     * @return \Improntus\ProntoPaga\PaymentMethods\Api\Data\PaymentMethodsInterface
     */
    public function setMethod($method);

    /**
     * Get currency
     * @return string|null
     */
    public function getCurrency();

    /**
     * Set currency
     * @param string $currency
     * @return \Improntus\ProntoPaga\PaymentMethods\Api\Data\PaymentMethodsInterface
     */
    public function setCurrency($currency);

    /**
     * Get logo
     * @return string|null
     */
    public function getLogo();

    /**
     * Set logo
     * @param string $logo
     * @return \Improntus\ProntoPaga\PaymentMethods\Api\Data\PaymentMethodsInterface
     */
    public function setLogo($logo);
}
