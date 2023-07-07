<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Improntus\ProntoPaga\Model;

use Improntus\ProntoPaga\Api\CallbackInterface;
use Improntus\ProntoPaga\Helper\Data as ProntoPagaHelper;
use Improntus\ProntoPaga\Model\Payment\Prontopaga as ProntoPaga;
use Magento\Sales\Api\Data\OrderInterface;

class Callback implements CallbackInterface
{

    /**
     * @var ProntoPagaHelper
     */
    private $prontoPagaHelper;

    /**
     * @var ProntoPaga
     */
    private $prontoPaga;

    /**
     * @var OrderInterface
     */
    private $orderInterface;

    /**
     * Constructor
     *
     * @param ProntoPagaHelper $prontoPagaHelper
     * @param ProntoPaga $prontoPaga
     * @param OrderInterface $orderInterface
     * @param RequestInterface $request
     */
    public function __construct(
        ProntoPagaHelper $prontoPagaHelper,
        ProntoPaga $prontoPaga,
        OrderInterface $orderInterface
    ) {
        $this->prontoPagaHelper = $prontoPagaHelper;
        $this->prontoPaga = $prontoPaga;
        $this->orderInterface = $orderInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function updateStatus($body)
    {
        try {
            $this->prontoPagaHelper->log(['type' => 'info', 'message' => $body, 'method' => __METHOD__]);
        } catch (\Exception $e) {
            $this->prontoPagaHelper->log(['type' => 'info', 'message' => $this->prontoPaga->json->serialize($body), 'method' => __METHOD__]);
        }
        return true;
    }
}
