<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Improntus\ProntoPaga\Controller\Adminhtml\Transaction;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Improntus\ProntoPaga\Service\ProntoPagaApiService as WebService;
use Improntus\ProntoPaga\Helper\Data as ProntoPagaHelper;
use Magento\Framework\Data\Form\FormKey\Validator;
use Improntus\ProntoPaga\Api\TransactionRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Validate extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Improntus_ProntoPaga::transaction_view';

    /**
     * @var bool|PageFactory
     */
    protected $resultJsonFactory = false;

    /**
     * @var WebService
     */
    private $webService;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionInterface;

    /**
     * @var Json
     */
    private $json;

    /**
     * Constructor
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param WebService $webService
     * @param Validator $formKeyValidator
     * @param TransactionRepositoryInterface $transactionInterface
     * @param Json $json
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        WebService $webService,
        Validator $formKeyValidator,
        TransactionRepositoryInterface $transactionInterface,
        Json $json
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->webService = $webService;
        $this->formKeyValidator = $formKeyValidator;
        $this->transactionInterface = $transactionInterface;
        $this->json = $json;
    }

    /**
     * @return Page|ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $uid =  $this->getRequest()->getParam('transactionId');

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $resultJson->setData([]);
        }

        $response = $this->webService->confirmPayment($uid);
        if (!in_array($response['code'], ProntoPagaHelper::STATUS_OK)) {
            return $resultJson->setData($response['body']['message']);
        }

        $this->updateTransaction($uid, $response);
        return $resultJson->setData($response);
    }

    /**
     * Update transacction status on grid
     *
     * @param string $uid
     * @param string $status
     * @return void
     */
    private function updateTransaction($uid, $response)
    {
        $transaction = $this->transactionInterface->getByTransactionId($uid);
        $response = $this->json->unserialize($response['body']['message']);
        $status = isset($response['status']) && $response['status'] ? $response['status'] : '';
        if ($transaction && $status) {
            $transaction->setStatus($status)->save();
        }

        return;
    }
}
