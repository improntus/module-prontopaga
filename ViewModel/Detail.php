<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Improntus\ProntoPaga\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Improntus\ProntoPaga\Model\ResourceModel\Transaction as TransactionResourceModel;
use Improntus\ProntoPaga\Model\TransactionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;


class Detail implements ArgumentInterface
{
    /**
     * @var TransactionResourceModel
     */
    private $transactionResourceModel;

    /**
     * @var TransactionFactory
     */
    private $transaction;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var TimezoneInterface
     */
    private $_timeZoneInterface;

    /**
     * @param TransactionResourceModel $transactionResourceModel
     * @param TransactionFactory $transaction
     * @param RequestInterface $request
     * @param TimezoneInterface $timeZoneInterface
     */
    public function __construct(
        TransactionResourceModel $transactionResourceModel,
        TransactionFactory $transaction,
        RequestInterface $request,
        TimezoneInterface $timeZoneInterface
    ) {
        $this->transactionResourceModel = $transactionResourceModel;
        $this->transaction = $transaction;
        $this->request = $request;
        $this->_timeZoneInterface = $timeZoneInterface;
    }

    /**
     *
     * @return \Improntus\ProntoPaga\Model\Transaction $transaction
     */
    public function getTransactionData()
    {
        $dataId = $this->request->getParam('entity_id');
        $transaction = $this->transaction->create();
        $this->transactionResourceModel->load($transaction, $dataId);
        return $transaction;
    }

    /**
     * Return locale formatted date
     *
     * @param string $date
     * @return string
     */
    public function getFormattedDate(string $date): string
    {
        $date = new \DateTime($date);
        return $this->_timeZoneInterface->formatDate($date, \IntlDateFormatter::LONG, true);
    }
}
