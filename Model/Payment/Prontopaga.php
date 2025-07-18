<?php

declare(strict_types=1);

namespace Improntus\ProntoPaga\Model\Payment;

use Improntus\ProntoPaga\Helper\Data as ProntoPagaHelper;
use Improntus\ProntoPaga\Service\ProntoPagaApiService as WebService;
use Improntus\ProntoPaga\Api\TransactionRepositoryInterface;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface as PaymentTransactionRepository;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Improntus\ProntoPaga\Model\TransactionFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\App\ResourceConnection;

/**
 * @author Improntus <http://www.improntus.com> - Adobe Gold Partner - Elevating digital experience
 * @copyright Copyright (c) 2025 Improntus
 */
class Prontopaga
{
    const SANDBOX_DOCUMENT = '11.111.111-1';
    const DOCUMENT_KEY = 'document_number';

    /**
     * @var ProntoPagaHelper
     */
    private $prontoPagaHelper;

    /**
     * @var WebService
     */
    public $webService;

    /**
     * @var InvoiceManagementInterface
     */
    private $invoiceManagement;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderPaymentRepositoryInterface
     */
    private $paymentRepository;

    /**
     * @var PaymentTransactionRepository
     */
    private $paymentTransactionRepository;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var TransactionRepositoryInterface
     */
    public $transactionRepository;

    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var Json
     */
    public $json;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     *
     * @param ProntoPagaHelper $prontoPagaHelper
     * @param WebService $webService
     * @param InvoiceManagementInterface $invoiceManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderPaymentRepositoryInterface $paymentRepository
     * @param PaymentTransactionRepository $paymentTransactionRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param OrderSender $orderSender
     * @param TransactionRepositoryInterface $transactionRepository
     * @param TransactionFactory $transactionFactory
     * @param Json $json
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ProntoPagaHelper $prontoPagaHelper,
        WebService  $webService,
        InvoiceManagementInterface $invoiceManagement,
        OrderRepositoryInterface $orderRepository,
        OrderPaymentRepositoryInterface $paymentRepository,
        PaymentTransactionRepository $paymentTransactionRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        OrderSender $orderSender,
        TransactionRepositoryInterface $transactionRepository,
        TransactionFactory $transactionFactory,
        Json $json,
        ResourceConnection $resourceConnection
    ) {
        $this->prontoPagaHelper = $prontoPagaHelper;
        $this->webService = $webService;
        $this->invoiceManagement = $invoiceManagement;
        $this->orderRepository = $orderRepository;
        $this->paymentRepository = $paymentRepository;
        $this->paymentTransactionRepository = $paymentTransactionRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->orderSender = $orderSender;
        $this->transactionRepository = $transactionRepository;
        $this->transactionFactory = $transactionFactory;
        $this->json = $json;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param array $order
     * @param string $selectedMethod
     * @return boolean|string|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function createTransaction($order, $selectedMethod)
    {
        $data = $this->getRequestData($order, $selectedMethod);
        $data['sign'] = $this->prontoPagaHelper->firmSecretKey($data);
        try {
            $response = $this->webService->createPayment($data);
        } catch (\Exception $e) {
            $this->prontoPagaHelper->log(['type' => 'error', 'message' => $e->getMessage(), 'method' => __METHOD__]);
            throw new \Exception($e->getMessage());
        }
        return $response ?: false;
    }

    /**
     * @param Order $order
     * @param string $selectedMethod
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getRequestData($order, $selectedMethod)
    {
        $customerData = $this->getCustomerData($order);
        $token = $this->prontoPagaHelper->encrypt($order->getEntityId(), true);
        return [
            'currency' => $this->prontoPagaHelper->getCurrency($order->getStore()->getId()),
            'country' => $this->prontoPagaHelper->getCountryCode(),
            'amount' => round((int)$order->getGrandTotal(), 0),
            'clientName' => $customerData['clientName'],
            'clientEmail' => $customerData['clientEmail'],
            'clientPhone' => $customerData['clientPhone'],
            'clientDocument' => $customerData['clientDocument'],
            'paymentMethod' => $selectedMethod,
            'urlConfirmation' => $this->prontoPagaHelper->getCallBackUrl(ProntoPagaHelper::STEP_PAYMENT),
            'urlFinal' =>  $this->prontoPagaHelper->getResponseUrl(['token' => $token, 'type' =>  ProntoPagaHelper::STATUS_FINAL]),
            'urlRejected' =>  $this->prontoPagaHelper->getResponseUrl(['token' => $token, 'type' =>  ProntoPagaHelper::STATUS_REJECTED]),
            'order' => $order->getIncrementId()
            // 'order' => rand(1, 2147483647)
        ];
    }

    /**
     * @param Order $order
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCustomerData($order)
    {
        $address = $order->getBillingAddress();
        $payment = $order->getPayment();
        return [
            'clientName' => "{$address->getFirstname()} {$address->getLastname()}",
            'clientEmail' => $order->getCustomerEmail(),
            'clientPhone' => $address->getTelephone() ?? '',
            'clientDocument' => $payment->getAdditionalInformation(self::DOCUMENT_KEY)
                ?? $order->getCustomerTaxvat()
                ?? '' //self::SANDBOX_DOCUMENT
        ];
    }

    /**
     * @param Order $order
     * @param mixed $response
     * @param string|null $flow
     * @return void
     * @throws LocalizedException
     */
    public function persistTransaction($order, $response = null, $flow = null)
    {
        try {
            // Default values for response and request bodies
            $responseBody = $response['body'] ?? '';
            $requestBody = $this->json->unserialize($response['request_body'] ?? '{}');

            // Retrieve transaction by order id or create a new one if it doesn't exist
            $transaction = $this->transactionRepository->getByOrderId($order->getEntityId())
                ?: $this->transactionFactory->create();

            // Set order id, transaction id and status
            $transaction->setOrderId($order->getEntityId());
            $transaction->setTransactionId($responseBody['uid'] ?? '');
            $transaction->setStatus($flow);

            // Set payment method if it's not already set
            if (!$transaction->getPaymentMethod()) {
                $transaction->setPaymentMethod($requestBody['paymentMethod'] ?? '');
            }

            // Set request body and request response
            $transaction->setRequestBody($response['request_body'] ?? $transaction->getRequestBody());
            $transaction->setRequestResponse(
                $this->getRequestResponse($transaction, $responseBody, $flow)
            );

            $this->transactionRepository->save($transaction);
        } catch (\Exception $e) {
            $this->prontoPagaHelper->log(['type' => 'error', 'message' => $e->getMessage(), 'method' => __METHOD__]);
        }
    }

    /**
     * Get request response based on the flow
     *
     * @param \Improntus\ProntoPaga\Api\Data\TransactionInterface $transaction
     * @param mixed $responseBody
     * @param string|null $flow
     * @return string
     */
    private function getRequestResponse($transaction, $responseBody, $flow)
    {
        if ($flow === ProntoPagaHelper::STATUS_REFUNDED) {
            $prevResponse = $this->json->unserialize($transaction->getRequestResponse());

            //check if array is multidimensional
            if (isset($prevResponse[0])) {
                $responseBody = array_merge($prevResponse, [$responseBody]);
            } else {
                $responseBody = [$prevResponse, $responseBody];
            }
        }

        return $this->json->serialize($responseBody);
    }

    /**
     * @param $order
     * @param $transactionId
     * @return bool
     */
    public function invoice($order, $transactionId)
    {
        if (!$order->canInvoice() || $order->hasInvoices()) {
            return false;
        }

        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();
        try {
            $invoice = $this->invoiceManagement->prepareInvoice($order);
            $invoice->register();
            $this->orderRepository->save($order);
            $invoice->setTransactionId($transactionId);
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $payment = $order->getPayment();
            $this->paymentRepository->save($payment);
            /** @var \Magento\Sales\Model\Order\Payment\Transaction $payment */
            $transaction = $this->generateTransaction($payment, $invoice, $transactionId);
            $transaction->setAdditionalInformation('amount', $order->getGrandTotal());
            $transaction->setAdditionalInformation('currency', $this->prontoPagaHelper->getCurrency());
            $this->paymentTransactionRepository->save($transaction);

            if (!$order->getEmailSent()) {
                $this->orderSender->send($order);
                $order->setIsCustomerNotified(true);
            }

            $invoice->pay();
            $invoice->getOrder()->setIsInProcess(true);
            $payment->addTransactionCommentsToOrder($transaction, __('Pronto Paga'));
            $this->invoiceRepository->save($invoice);
            $message = (__('Payment confirmed by Pronto Paga. Invoice generated automatically.'));
            $order->addCommentToStatusHistory($message, Order::STATE_PROCESSING);
            $this->orderRepository->save($order);
            $connection->commit();
        } catch (\Exception $e) {
            $this->prontoPagaHelper->log(['type' => 'error', 'message' => $e->getMessage(), 'method' => __METHOD__]);
            $connection->rollBack();
            $this->prontoPagaHelper->log(
                [
                    'type' => 'error',
                    'message' => "Invoice creating for order {$order->getIncrementId()} failed: {$e->getMessage()}",
                    'method' => __METHOD__
                ]
            );
            return false;
        }

        return true;
    }

    /**
     * @param $payment
     * @param $invoice
     * @param $paypalTransaction
     * @return mixed
     */
    private function generateTransaction($payment, $invoice, $transactionId)
    {
        $payment->setTransactionId($transactionId);
        return $payment->addTransaction(TransactionInterface::TYPE_CAPTURE, $invoice, true);
    }

    /**
     * @param Order $order
     * @param Phrase $message
     * @return bool
     */
    public function cancelOrder($order, $message)
    {
        try {
            if ($order->canCancel()) {
                $order->cancel();
                $order->setState(Order::STATE_CANCELED);
                $order->addCommentToStatusHistory($message, Order::STATE_CANCELED);
                $this->orderRepository->save($order);
                return true;
            }
        } catch (\Exception $e) {
            $this->prontoPagaHelper->log(['type' => 'error', 'message' => $e->getMessage(), 'method' => __METHOD__]);
            return false;
        }
        return false;
    }

    /**
     * @param OrderInterface $order
     * @param string $origin
     * @return void
     */
    public function addSuccessToStatusHistory($order, $origin)
    {
        /** @var \Magento\Sales\Model\Order $order */
        if ($order->getState() === Order::STATE_NEW) {
            $message = (__('Payment confirmed by Pronto Paga from %1, awaiting capture.', $origin));
            $order->addCommentToStatusHistory($message, Order::STATE_PAYMENT_REVIEW);
            $this->orderRepository->save($order);
        }
    }
}
