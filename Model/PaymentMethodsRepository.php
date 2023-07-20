<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Improntus\ProntoPaga\Model;

use Improntus\ProntoPaga\Api\Data\PaymentMethodsInterface;
use Improntus\ProntoPaga\Api\Data\PaymentMethodsInterfaceFactory;
use Improntus\ProntoPaga\Api\Data\PaymentMethodsSearchResultsInterfaceFactory;
use Improntus\ProntoPaga\Api\PaymentMethodsRepositoryInterface;
use Improntus\ProntoPaga\Model\ResourceModel\PaymentMethods as ResourcePaymentMethods;
use Improntus\ProntoPaga\Model\ResourceModel\PaymentMethods\CollectionFactory as PaymentMethodsCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class PaymentMethodsRepository implements PaymentMethodsRepositoryInterface
{

    /**
     * @var PaymentMethods
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var ResourcePaymentMethods
     */
    protected $resource;

    /**
     * @var PaymentMethodsCollectionFactory
     */
    protected $paymentMethodsCollectionFactory;

    /**
     * @var PaymentMethodsInterfaceFactory
     */
    protected $paymentMethodsFactory;


    /**
     * @param ResourcePaymentMethods $resource
     * @param PaymentMethodsInterfaceFactory $paymentMethodsFactory
     * @param PaymentMethodsCollectionFactory $paymentMethodsCollectionFactory
     * @param PaymentMethodsSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourcePaymentMethods $resource,
        PaymentMethodsInterfaceFactory $paymentMethodsFactory,
        PaymentMethodsCollectionFactory $paymentMethodsCollectionFactory,
        PaymentMethodsSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->paymentMethodsFactory = $paymentMethodsFactory;
        $this->paymentMethodsCollectionFactory = $paymentMethodsCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(PaymentMethodsInterface $paymentMethods)
    {
        try {
            $this->resource->save($paymentMethods);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the paymentMethods: %1',
                $exception->getMessage()
            ));
        }
        return $paymentMethods;
    }

    /**
     * @inheritDoc
     */
    public function get($entityId)
    {
        $paymentMethods = $this->paymentMethodsFactory->create();
        $this->resource->load($paymentMethods, $entityId);
        if (!$paymentMethods->getId()) {
            throw new NoSuchEntityException(__('payment_methods with id "%1" does not exist.', $entityId));
        }
        return $paymentMethods;
    }

     /**
     * @inheritDoc
     */
    public function getByMethod($method)
    {
        $paymentMethods = $this->paymentMethodsFactory->create();
        $this->resource->load($paymentMethods, $method, 'method');
        if (!$paymentMethods->getId()) {
            return false;
        }
        return $paymentMethods;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->paymentMethodsCollectionFactory->create();

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
    public function delete(PaymentMethodsInterface $paymentMethods)
    {
        try {
            $paymentMethodsModel = $this->paymentMethodsFactory->create();
            $this->resource->load($paymentMethodsModel, $paymentMethods->getEntityId());
            $this->resource->delete($paymentMethodsModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the payment_methods: %1',
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

