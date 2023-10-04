<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Improntus\ProntoPaga\Controller\Adminhtml\Transaction;

class Index extends \Magento\Backend\App\Action
{

    const ADMIN_RESOURCE = 'Improntus_ProntoPaga::transactions_page_view';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var string
     */
    private $menuId;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        $menuId = 'Improntus_ProntoPaga::transactions_page_view'
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->menuId = $menuId;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->initLayout();
        $this->_setActiveMenu($this->menuId);
        $resultPage->getConfig()->getTitle()->prepend(__("Pronto Paga Transactions"));
        return $resultPage;
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        return parent::_isAllowed();
    }
}
