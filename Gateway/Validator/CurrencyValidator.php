<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Improntus\ProntoPaga\Gateway\Validator;

use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Improntus\ProntoPaga\Helper\Data as ProntoPagaHelper;

class CurrencyValidator extends AbstractValidator
{
    /**
     * @var ProntoPagaHelper
     */
    private $prontoPagaHelper;

    /**
     * Constructor
     *
     * @param ResultInterfaceFactory $resultInterfaceFactory
     * @param ProntoPagaHelper $prontoPagaHelper
     */
    public function __construct(
        ResultInterfaceFactory $resultInterfaceFactory,
        ProntoPagaHelper $prontoPagaHelper
    ) {
        parent::__construct($resultInterfaceFactory);
        $this->prontoPagaHelper = $prontoPagaHelper;
    }

    /**
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $isValid = true;
        $fails = [];
        if ($validationSubject['currency'] != $this->prontoPagaHelper->getCurrency($validationSubject['storeId'])) {
            $isValid = false;
            $fails[] = __('Currency doesn\'t match.');
        }
        return $this->createResult($isValid, $fails);
    }
}
