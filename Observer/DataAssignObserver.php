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
    /**
     * @var array
     */
    private $keys = [
        'document_number'
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
           'document_number',
            $additionalData['document_number']
        );

        foreach ($data as $key => $value) {
            $paymentModel->setData($key, $value);
        }

        return $this;
    }
}
