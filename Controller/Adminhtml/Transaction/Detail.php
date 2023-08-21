<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Improntus\ProntoPaga\Controller\Adminhtml\Transaction;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Improntus\ProntoPaga\Api\TransactionRepositoryInterface;

class Detail extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Improntus_ProntoPaga::transactions_page_view';

    /**
     * @var bool|PageFactory
     */
    protected $resultPageFactory = false;

    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactionInterface;

   /**
    * Constructor
    *
    * @param Context $context
    * @param PageFactory $resultPageFactory
    * @param TransactionRepositoryInterface $transactionInterface
    */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        TransactionRepositoryInterface $transactionInterface
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->transactionInterface = $transactionInterface;
    }

    /**
     * @return Page|ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $id = $this->_request->getParam('entity_id');
        $model = $this->transactionInterface->get($id);
        $resultPage->getConfig()->getTitle()->prepend($model ? __('Transaction Details uid: %1', $model->getTransactionId()) : __('Transaction Details'));
        return $resultPage;
    }
}
