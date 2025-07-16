<?php

namespace Improntus\ProntoPaga\Controller\Order;

use Magento\Framework\App\ActionInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Improntus\ProntoPaga\Model\Payment\Prontopaga;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Improntus\ProntoPaga\Helper\Data as ProntoPagaHelper;
use Magento\Framework\App\RequestInterface;

/**
 * @author Improntus <http://www.improntus.com> - Adobe Gold Partner - Elevating digital experience
 * @copyright Copyright (c) 2025 Improntus
 */
class Create implements ActionInterface
{
    const ERROR = 'error';
    const FAILRURE_PATH = 'checkout/onepage/failure';

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
        string $flow = 'created'
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
        try {
            /** @var \Magento\Sales\Model\Order $oder */
            $order = $this->checkoutSession->getLastRealOrder();
            $selectedMethod = $this->request->getParam('method');
            $resultRedirect = $this->redirectFactory->create();

            if ($response = $this->prontoPaga->createTransaction($order, $selectedMethod)) {
                if (in_array($response['code'], ProntoPagaHelper::STATUS_UNAUTHORIZED)) {
                    if (isset($response['body']['error'])) {
                        $errorKey = key($response['body']['error']);
                        $error = $response['body']['error'][$errorKey] ?? '';
                    }
                    $message = (__("Order %1 error: %2", $order->getIncrementId(), $error ?? ''));
                    $this->prontoPagaHelper->log(['type' => 'info', 'message' => $message, 'method' => __METHOD__]);
                    $response['urlPay'] = $this->prontoPagaHelper->getResponseUrl();
                    $this->flow =  'error';
                }
            }

            $this->prontoPaga->persistTransaction($order, $response, $this->flow);
            $url = $response['urlPay'] ?? $response['body']['urlPay'];
            $resultRedirect->setUrl($url);
        } catch (\Exception $e) {
            $this->prontoPagaHelper->log(['type' => 'error', 'message' => $e->getMessage(), 'method' => __METHOD__]);
            $this->checkoutSession->setErrorMessage(__('An error occurred while processing your request. Please try again later.'));
            $resultRedirect->setPath(self::FAILRURE_PATH);
        }
        return $resultRedirect;
    }
}
