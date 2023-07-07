<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Improntus\ProntoPaga\Model;

use Improntus\ProntoPaga\Api\CallbackInterface;
use Improntus\ProntoPaga\Helper\Data as ProntoPagaHelper;
use Improntus\ProntoPaga\Model\Payment\Prontopaga as ProntoPaga;
use Magento\Sales\Model\Order;
use Magento\Framework\Webapi\Rest\Request;

use function PHPSTORM_META\type;

class Callback implements CallbackInterface
{
    /**
     * Initial state of application.
     */
    const STATUS_NEW = 'new';

    /**
     * Status when the user selects the corresponding payment method.
     */
    const STATUS_CREATED = 'created';

    /**
     * Indicates that the transaction was successful.
     */
    const STATUS_SUCCESS = 'success';

    /**
     * The user has canceled the transaction.
     */
    const STATUS_CANCELED = 'canceled';

    /**
     * Request rejected by payment method.
     */
    const STATUS_REJECT = 'reject';

    /**
     * Request pending approval by payment method.
     */
    const STATUS_PENDING = 'pending';

    /**
     * Expires pending requests after x amount of time.
     */
    const STATUS_EXPIRED = 'expired';

    /**
     * @var ProntoPagaHelper
     */
    private $prontoPagaHelper;

    /**
     * @var ProntoPaga
     */
    private $prontoPaga;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Constructor
     *
     * @param ProntoPagaHelper $prontoPagaHelper
     * @param ProntoPaga $prontoPaga
     * @param Order $order
     * @param Request $request
     */
    public function __construct(
        ProntoPagaHelper $prontoPagaHelper,
        ProntoPaga $prontoPaga,
        Order $order,
        Request $request
    ) {
        $this->prontoPagaHelper = $prontoPagaHelper;
        $this->prontoPaga = $prontoPaga;
        $this->order = $order;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function confirmOrder()
    {
        $bodyParams = $this->request->getBodyParams();
        $serializedBodyParams = $this->prontoPaga->json->serialize($bodyParams);
        /** @var Order $order */
        $order = $this->getOrder($bodyParams['order']);
        $status = $bodyParams['status'] ?? false;
        $transactionId =  $bodyParams['uid'] ?? false;

        if (!$status || !$transactionId) {
            return false;
        }

        switch ($status) {
            case self::STATUS_NEW:
                $this->prontoPagaHelper->log(['type' => 'error', 'message' => self::STATUS_NEW, 'method' => __METHOD__]);
                break;
            case self::STATUS_CREATED:
                $this->prontoPagaHelper->log(['type' => 'error', 'message' => self::STATUS_CREATED, 'method' => __METHOD__]);
                break;
            case self::STATUS_SUCCESS:
                $this->successProcess($order, $transactionId);
                break;
            case self::STATUS_CANCELED:
                $this->cancelProcess($order, self::STATUS_CANCELED);
                break;
            case self::STATUS_REJECT:
                $this->cancelProcess($order, self::STATUS_REJECT);
                break;
            case self::STATUS_PENDING:
                $this->prontoPagaHelper->log(['type' => 'error', 'message' => self::STATUS_PENDING, 'method' => __METHOD__]);
                break;
            case self::STATUS_EXPIRED:
                $this->cancelProcess($order, self::STATUS_EXPIRED);
                break;
        }

        $this->prontoPaga->persistTransaction($order, $serializedBodyParams, $status);
        return true;
    }

    /**
     * Load order by increment_id
     *
     * @param string $incrementId
     * @return Order
     */
    private function getOrder(string $incrementId): Order
    {
        return $this->order->loadByIncrementId($incrementId);
    }

    /**
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    private function successProcess($order, $transactionId): bool
    {
        try {
            $this->prontoPaga->addSuccessToStatusHistory($order);
            $this->prontoPaga->invoice($order, $transactionId);
        } catch (\Exception $e) {
            $this->prontoPagaHelper->log(['type' => 'error', 'message' => $e->getMessage(), 'method' => __METHOD__]);
            new \Magento\Framework\Webapi\Exception(__('Order could not be invoiced.'));
            return false;
        }
        return true;
    }

    /**
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    private function cancelProcess($order, $type): bool
    {
        try {
            $message = $type === self::STATUS_CANCELED
                            ?  __('Order canceled by user.')
                            :  __('There was a problem retrieving payment confirmation from Pronto Paga.');
            $this->prontoPaga->cancelOrder($order, $message);
        } catch (\Exception $e) {
            $this->prontoPagaHelper->log(['type' => 'error', 'message' => $e->getMessage(), 'method' => __METHOD__]);
            new \Magento\Framework\Webapi\Exception(__('Order could not be invoiced.'));
            return false;
        }
        return true;
    }
}
