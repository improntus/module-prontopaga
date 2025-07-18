<?php

namespace Improntus\ProntoPaga\Model;

use Improntus\ProntoPaga\Helper\Data as ProntoPagaHelper;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @author Improntus <http://www.improntus.com> - Adobe Gold Partner - Elevating digital experience
 * @copyright Copyright (c) 2025 Improntus
 */
class ConfigProvider implements ConfigProviderInterface
{
    const BANNER = 'Improntus_ProntoPaga::images/logo.png';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AssetRepository
     *
     */
    private $assetRepository;

    /**
     * @var ProntoPagaHelper
     */
    private $prontoPagaHelper;

    /**
     * Constructor
     *
     * @param AssetRepository $assetRepository
     * @param ProntoPagaHelper $prontoPagaHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        AssetRepository $assetRepository,
        ProntoPagaHelper $prontoPagaHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        $this->assetRepository = $assetRepository;
        $this->prontoPagaHelper = $prontoPagaHelper;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getConfig()
    {
        return [
            'payment' => [
                ProntoPagaHelper::PAYMENT_CODE => [
                    'active' => $this->prontoPagaHelper->isEnabled() && $this->prontoPagaHelper->validateCredentials(),
                    'redirect_url' => $this->prontoPagaHelper->getRedirectUrl(),
                    'title' => $this->prontoPagaHelper->getTitle(),
                    'logo' => $this->prontoPagaHelper->getLogo() ?: $this->assetRepository->getUrl(self::BANNER),
                    'code' =>  ProntoPagaHelper::PAYMENT_CODE,
                    'allowed_methods' => $this->prontoPagaHelper->getAllowedMethods(),
                    'methods_img_url' => $this->prontoPagaHelper->getMethodsImgUrl(),
                    'use_document_field' => $this->prontoPagaHelper->useDocumentField(),
                    'is_field_required' => $this->prontoPagaHelper->isFieldRequired()
                ]
            ],
        ];
    }
}
