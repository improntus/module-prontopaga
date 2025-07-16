<?php

declare(strict_types=1);

namespace Improntus\ProntoPaga\Model;

use Improntus\ProntoPaga\Api\Data\TransactionInterface;
use Improntus\ProntoPaga\Api\Data\TransactionInterfaceFactory;
use Improntus\ProntoPaga\Api\Data\TransactionSearchResultsInterfaceFactory;
use Improntus\ProntoPaga\Api\TransactionRepositoryInterface;
use Improntus\ProntoPaga\Model\ResourceModel\Transaction as ResourceTransaction;
use Improntus\ProntoPaga\Model\ResourceModel\Transaction\CollectionFactory as TransactionCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @author Improntus <http://www.improntus.com> - Adobe Gold Partner - Elevating digital experience
 * @copyright Copyright (c) 2025 Improntus
 */
class TransactionRepository implements TransactionRepositoryInterface
{

    /**
     * @var Transaction
     */
    protected $searchResultsFactory;

    /**
     * @var TransactionInterfaceFactory
     */
    protected $transactionFactory;

    /**
     * @var TransactionCollectionFactory
     */
    protected $transactionCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var ResourceTransaction
     */
    protected $resource;


    /**
     * @param ResourceTransaction $resource
     * @param TransactionInterfaceFactory $transactionFactory
     * @param TransactionCollectionFactory $transactionCollectionFactory
     * @param TransactionSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceTransaction $resource,
        TransactionInterfaceFactory $transactionFactory,
        TransactionCollectionFactory $transactionCollectionFactory,
        TransactionSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->transactionFactory = $transactionFactory;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(TransactionInterface $transaction)
    {
        try {
            $this->resource->save($transaction);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the transaction: %1',
                $exception->getMessage()
            ));
        }
        return $transaction;
    }

    /**
     * @inheritDoc
     */
    public function get($entityId)
    {
        $transaction = $this->transactionFactory->create();
        $this->resource->load($transaction, $entityId);
        if (!$transaction->getEntityId()) {
            throw new NoSuchEntityException(__('transaction with id "%1" does not exist.', $entityId));
        }
        return $transaction;
    }

    /**
     * @inheritDoc
     */
    public function getByTransactionId($transactionId)
    {
        $transaction = $this->transactionFactory->create();
        $this->resource->load($transaction, $transactionId, 'transaction_id');
        if (!$transaction->getEntityId()) {
            return false;
        }
        return $transaction;
    }

    /**
     * @inheritDoc
     */
    public function getByOrderId($orderId)
    {
        $transaction = $this->transactionFactory->create();
        $this->resource->load($transaction, $orderId, 'order_id');
        if (!$transaction->getEntityId()) {
            return false;
        }
        return $transaction;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->transactionCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(TransactionInterface $transaction)
    {
        try {
            $transactionModel = $this->transactionFactory->create();
            $this->resource->load($transactionModel, $transaction->getEntityId());
            $this->resource->delete($transactionModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the transaction: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($entityId)
    {
        return $this->delete($this->get($entityId));
    }
}
