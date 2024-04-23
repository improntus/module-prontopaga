<?php
/*
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Improntus\ProntoPaga\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

class DataAssignObserver extends AbstractDataAssignObserver
{
    const DOCUMENT_NUMBER = 'document_number';

    /**
     * @var array
     */
    private $keys = [
        self::DOCUMENT_NUMBER
    ];

    /**
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $dataObject = $this->readDataArgument($observer);
        $additionalData = $dataObject->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!is_array($additionalData)) {
            return;
        }

        $data = array_intersect_key($additionalData, array_flip($this->keys));
        if (count($data) !== count($this->keys)) {
            return;
        }

        $paymentModel = $this->readPaymentModelArgument($observer);
        $paymentModel->setAdditionalInformation(
            self::DOCUMENT_NUMBER,
            $additionalData[self::DOCUMENT_NUMBER]
        );

        foreach ($data as $key => $value) {
            $paymentModel->setData($key, $value);
        }

        return $this;
    }
}
