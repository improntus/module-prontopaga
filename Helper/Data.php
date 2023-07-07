<?php
/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Improntus\ProntoPaga\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Improntus\ProntoPaga\Logger\Logger;
use Magento\Store\Model\StoreManagerInterface;

use function Safe\base64_decode;

class Data extends AbstractHelper
{
    /** class consts */
    const INCOMPLETE_CREDENTIALS = 0;
    const USER_AUTHENTICATED = 1;
    const LOGGER_NAME = 'prontopaga';
    const UPLOAD_DIR = 'prontopaga/';
    const METHODS_IMG_PATH = 'Improntus_ProntoPaga/images/methods';
    const STATUS_OK = [200, 201];
    const STATUS_UNAUTHORIZED = [400, 401, 403];
    const STATUS_CONFIRMATION = 'confirmation';
    const STATUS_FINAL = 'final';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ERROR = 'error';

    /** Configuration path for Pronto Paga payment section */
    const XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_ACTIVE  = 'payment/prontopaga/active';
    const XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_TITLE = 'payment/prontopaga/title';
    const XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_SPECIFICMETHODS = 'payment/prontopaga/specificmethods';
    const XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_DEBUG = 'payment/prontopaga/debug';
    const XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_API_ENDPOINT = 'payment/prontopaga/endpoint';
    const XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_API_TOKEN = 'payment/prontopaga/token';
    const XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_SECRET_KEY = 'payment/prontopaga/secret_key';
    const XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_LOGO = 'payment/prontopaga/logo';

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Helper Constructor
     *
     * @param Context $context
     * @param EncryptorInterface $encryptor
     * @param Logger $logger
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        EncryptorInterface $encryptor,
        Logger $logger,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->encryptor = $encryptor;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
    }

    /**
     *
     * @param string $field
     * @param int|null $storeId
     * @return string|null
     */
    public function getConfigValue(string $field, $storeId = null)
	{
		return $this->scopeConfig->getValue(
			$field, ScopeInterface::SCOPE_STORE, $storeId
		);
	}

    /**
     * Retrieve if payment method is enabled
     *
     * @return boolean
     */
    public function isEnabled(): bool
    {
        return (bool)$this->getConfigValue(self::XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_ACTIVE);
    }

    /**
     * Retrieve payment method title
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->getConfigValue(self::XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_TITLE);
    }

    /**
     * Retrieve allowed payment methods
     *
     * @return string
     */
    public function getAllowedMethods()
    {
        $methods = $this->getConfigValue(self::XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_SPECIFICMETHODS) ?? '';
        return $methods ? explode(',', $methods) : [];
    }

    /**
     * Retrieve API endpoint
     *
     * @return string
     */
    public function getApiEndpoint(): string
    {
        return $this->getConfigValue(self::XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_API_ENDPOINT);
    }

    /**
     * Retrieve API authorization token
     *
     * @return string
     */
    public function getApiToken(): string
    {
        return $this->getConfigValue(self::XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_API_TOKEN) ?? '';
    }

     /**
     * Retrieve secret key
     *
     * @return string
     */
    public function getSecretKey(): string
    {
        return $this->getConfigValue(self::XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_SECRET_KEY) ?? '';
    }

    /**
     * Retrieve if sandbox mode is enabled
     *
     * @return string|null
     */
    public function getLogo()
    {
        if ($filePath = $this->getConfigValue(self::XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_LOGO)) {
            return $this->storeManager->getStore()
                            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . self::UPLOAD_DIR  .  $filePath;
        }

        return $filePath;
    }

    /**
     * Retrieve if sandbox mode is enabled
     *
     * @return string|null
     */
    public function getMethodsImgUrl()
    {
        return self::METHODS_IMG_PATH;
    }

    /**
     * Retrieve if debug is enabled
     *
     * @return boolean
     */
    public function isDebugEnabled(): bool
    {
        return (bool)$this->getConfigValue(self::XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_DEBUG);
    }

     /**
      * Validate credentials
      *
      * @return integer
      */
    public function validateCredentials(): int
    {
        if ($this->getApiEndpoint() && $this->getApiToken()
            && $this->getSecretKey() && $this->getAllowedMethods()) {
            return self::USER_AUTHENTICATED;
        }
        return self::INCOMPLETE_CREDENTIALS;
    }

     /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRedirectUrl(): string
    {
        return $this->_getUrl('prontopaga/order/create', ['_secure' => 'true']);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCallBackUrl(): string
    {
        return $this->_getUrl(null, [
                '_path' => 'enquiry',
                '_secure' => true,
                '_direct' => 'rest/V1/prontopaga/callback'
            ]
        );
    }

    /**
     * @param $token
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getResponseUrl($params = []): string
    {
        return $this->_getUrl('prontopaga/order/response', $params);
    }

    /**
     *
     * @param array $data
     * @return void
     * @see https://sandbox.insospa.com/files/documentation/index.php?lang=es#sign-with-your-secretkey
     */
    public function firmSecretKey(array $data)
    {
        $keys = array_keys($data);
		sort($keys);
		$toSign = '';
		foreach ($keys as $key) {
			$toSign .= $key . $data[$key];
		}

        return hash_hmac('sha256', $toSign, $this->getSecretKey());
    }

    /**
     *
     * @param array $data
     * @return void
     */
    public function log(array $data): void
    {
        if ($this->isDebugEnabled()) {
            $this->logger->setName(self::LOGGER_NAME);
            $data['type'] !== 'debug'
                ? $this->logger->{$data['type']}($data['message'], ['method_context' => $data['method']])
                : $this->logger->debug($data['message'], ['method_context' => $data['method']]);
        }
        return;
    }

    /**
     * Encrypt params
     *
     * @param string $params
     * @param bool $base64
     * @return string
     */
    public function encrypt(string $params, bool $base64 = false): string
    {
        if ($base64) {
            return base64_encode($this->encryptor->encrypt($params));
        }
        return $this->encryptor->encrypt($params);
    }

    /**
     * Decrypt params
     *
     * @param string $params
     * @param bool $base64
     * @return string
     */
    public function decrypt(string $params, bool $base64 = false): string
    {
        if ($base64) {
            return $this->encryptor->decrypt(base64_decode($params));
        }
        return $this->encryptor->decrypt($params);
    }

    /**
     * Retrieve store currency code
     *
     * @param string|int $storeId
     * @return string
     */
    public function getCurrency($storeId = null): string
    {
        return $this->storeManager->getStore($storeId)->getCurrentCurrency()->getCode();
    }

}
