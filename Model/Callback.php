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
use Magento\Framework\Webapi\Rest\Request;

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
     * @var Request
     */
    protected $request;

    /**
     * Constructor
     *
     * @param ProntoPagaHelper $prontoPagaHelper
     * @param ProntoPaga $prontoPaga
     * @param OrderInterface $orderInterface
     * @param Request $request
     */
    public function __construct(
        ProntoPagaHelper $prontoPagaHelper,
        ProntoPaga $prontoPaga,
        OrderInterface $orderInterface,
        Request $request
    ) {
        $this->prontoPagaHelper = $prontoPagaHelper;
        $this->prontoPaga = $prontoPaga;
        $this->orderInterface = $orderInterface;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function confirmOrder()
    {
        $bodyParams = $this->request->getBodyParams();
        try {
            $this->prontoPagaHelper->log(['type' => 'info', 'message' => $bodyParams, 'method' => __METHOD__]);
        } catch (\Exception $e) {
            $this->prontoPagaHelper->log(['type' => 'info', 'message' => $this->prontoPaga->json->serialize($bodyParams), 'method' => __METHOD__]);
        }
        return true;
    }
}
