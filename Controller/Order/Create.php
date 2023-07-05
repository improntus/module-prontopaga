<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Improntus\ProntoPaga\Controller\Order;

use Magento\Framework\App\ActionInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Improntus\ProntoPaga\Model\Payment\Prontopaga;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Improntus\ProntoPaga\Helper\Data as ProntoPagaHelper;
use Magento\Framework\App\RequestInterface;

class Create implements ActionInterface
{
    const ERROR = 'error';

    /**
     * @var ProntoPagaHelper
     */
    private $prontoPagaHelper;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var Prontopaga
     */
    private $prontoPaga;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var string
     */
    private $flow;

    /**
     * Constructor
     *
     * @param ProntoPagaHelper $prontoPagaHelper
     * @param CheckoutSession $checkoutSession
     * @param Prontopaga $prontoPaga
     * @param RedirectFactory $redirectFactory
     * @param RequestInterface $request
     * @param string $flow
     */
    public function __construct(
        ProntoPagaHelper $prontoPagaHelper,
        CheckoutSession $checkoutSession,
        Prontopaga $prontoPaga,
        RedirectFactory $redirectFactory,
        RequestInterface $request,
        string $flow = 'create'
    ) {
        $this->prontoPagaHelper = $prontoPagaHelper;
        $this->checkoutSession = $checkoutSession;
        $this->prontoPaga = $prontoPaga;
        $this->redirectFactory = $redirectFactory;
        $this->request = $request;
        $this->flow = $flow;
    }


    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        /** @var \Magento\Sales\Model\Order $oder */
        $order = $this->checkoutSession->getLastRealOrder();
        $selectedMethod = $this->request->getParam('method');
        $resultRedirect = $this->redirectFactory->create();

        if ($response = $this->prontoPaga->createTransaction($order, $selectedMethod)) {
            if (in_array($response['code'], ProntoPagaHelper::STATUS_UNAUTHORIZED)) {
                $message = "Order {$order->getIncrementId()} errors: {$response['body']['message']}";
                $this->prontoPagaHelper->log(['type' => 'info', 'message' => $message, 'method' => __METHOD__]);
                $this->checkoutSession->setProntoPagaError($message);
                $response['urlPay'] = $this->prontoPagaHelper->getCallBackUrl([
                    'token' => $this->prontoPagaHelper->encrypt($order->getEntityId()),
                    'type' =>  self::ERROR
                ]);
                $this->flow =  'error';
            }
        }

        $this->prontoPaga->persistTransaction($order, $response, $this->flow);
        $url = $response['urlPay'] ?? $this->prontoPaga->json->unserialize($response['body']['message'])['urlPay'];
        $resultRedirect->setUrl($url);
        return $resultRedirect;
    }
}
