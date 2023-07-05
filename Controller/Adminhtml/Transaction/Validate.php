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
     * @param Context $context
     * @param PageFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        WebService $webService,
        Validator $formKeyValidator
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->webService = $webService;
        $this->formKeyValidator = $formKeyValidator;
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
        return $resultJson->setData($response);
    }
}
