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
use Improntus\ProntoPaga\Api\PaymentMethodsRepositoryInterface as PaymentMethodsInterface;
use Magento\Store\Model\StoreManagerInterface;

use function Safe\base64_decode;

class Data extends AbstractHelper
{
    /** class consts */
    const INCOMPLETE_CREDENTIALS = 0;
    const USER_AUTHENTICATED = 1;
    const LOGGER_NAME = 'prontopaga';
    const UPLOAD_DIR = 'prontopaga/';
    const STATUS_OK = [200, 201];
    const STATUS_UNAUTHORIZED = [400, 401, 403];
    const STATUS_CONFIRMATION = 'confirmation';
    const STATUS_SUCCESS = 'success';
    const STATUS_FINAL = 'final';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ERROR = 'error';
    const STATUS_CANCELED = 'canceled';
    const STATUSES_SUCCESS = [self::STATUS_CONFIRMATION, self::STATUS_SUCCESS, self::STATUS_FINAL];
    const STATUSES_CANCEL = [self::STATUS_REJECTED, self::STATUS_ERROR, self::STATUS_CANCELED];

    /** Configuration path for Pronto Paga payment section */
    const XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_ACTIVE  = 'payment/prontopaga/active';
    const XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_TITLE = 'payment/prontopaga/title';
    const XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_SPECIFICMETHODS = 'payment/prontopaga/specificmethods';
    const XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_VALIDATE_ON_CHECKOUT = 'payment/prontopaga/validate_checkout';
    const XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_CUSTOM_PAGES = 'payment/prontopaga/custom_pages';
    const XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_DOCUMENT_FIELD = 'payment/prontopaga/document_field';
    const XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_FIELD_REQUIRED = 'payment/prontopaga/field_required';
    const XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_API_ENDPOINT = 'payment/prontopaga/endpoint';
    const XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_API_TOKEN = 'payment/prontopaga/token';
    const XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_SECRET_KEY = 'payment/prontopaga/secret_key';
    const XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_LOGO = 'payment/prontopaga/logo';
    const XML_PATH_IMPRONTUS_PRONTOPAGO_CANCEL_ORDERS_ACTIVE = 'payment/prontopaga/cancel_orders/active';
    const XML_PATH_IMPRONTUS_PRONTOPAGO_CANCEL_ORDERS_TIMEINTERVAL = 'payment/prontopaga/cancel_orders/timeinterval';

    /** Get country path */
    const COUNTRY_CODE_PATH = 'general/country/default';

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var PaymentMethodsInterface
     */
    private $paymentMethodsInterface;

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
     * @param PaymentMethodsInterface $paymentMethodsInterface
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        EncryptorInterface $encryptor,
        Logger $logger,
        PaymentMethodsInterface $paymentMethodsInterface,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->encryptor = $encryptor;
        $this->logger = $logger;
        $this->paymentMethodsInterface = $paymentMethodsInterface;
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
     * @return array
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
     * Return logo img path
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
     * Retrieve if cron is enabled
     *
     * @return bool
     */
    public function isCronEnabled()
    {
        return (bool) $this->getConfigValue(self::XML_PATH_IMPRONTUS_PRONTOPAGO_CANCEL_ORDERS_ACTIVE);
    }

    /**
     * Retrieve time interval for search pending orders
     *
     * @return string|null
     */
    public function getTimeInterval()
    {
        return $this->getConfigValue(self::XML_PATH_IMPRONTUS_PRONTOPAGO_CANCEL_ORDERS_TIMEINTERVAL);
    }

    /**
     * Retrieve img path
     *
     * @return array
     */
    public function getMethodsImgUrl()
    {
        foreach ($this->getAllowedMethods() as $method) {
            $methodsImg[$method] = [
                    'img' => $this->paymentMethodsInterface->getByMethod($method)->getLogo()
            ];
        }

        return $methodsImg ?? [];
    }

    /**
     * Retrieve if might be validate order on checkout page
     *
     * @return boolean
     */
    public function validateOnCheckout(): bool
    {
        return (bool)$this->getConfigValue(self::XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_VALIDATE_ON_CHECKOUT);
    }

    /**
     * Retrieve if might be use custom success page
     *
     * @return boolean
     */
    public function useCustomPages(): bool
    {
        return (bool)$this->getConfigValue(self::XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_CUSTOM_PAGES);
    }

     /**
     * Retrieve if might be use document number input
     *
     * @return boolean
     */
    public function useDocumentField(): bool
    {
        return (bool)$this->getConfigValue(self::XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_DOCUMENT_FIELD);
    }

     /**
     * Retrieve if document number field is required
     *
     * @return boolean
     */
    public function isFieldRequired(): bool
    {
        return (bool)$this->getConfigValue(self::XML_PATH_IMPRONTUS_PRONTOPAGO_PAYMENT_FIELD_REQUIRED);
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
     * @param array|string $params
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
     * @return string
     * @see https://sandbox.insospa.com/files/documentation/index.php?lang=es#sign-with-your-secretkey
     */
    public function firmSecretKey(array $data): string
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
        $this->logger->setName(self::LOGGER_NAME);
        $data['type'] !== 'debug'
            ? $this->logger->{$data['type']}($data['message'], ['method_context' => $data['method']])
            : $this->logger->debug($data['message'], ['method_context' => $data['method']]);
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
     * Get Country code by store scope
     *
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->getConfigValue(
            self::COUNTRY_CODE_PATH
        );
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

    /**
     * Validate request callback
     *
     * @param array $response
     * @return bool
     */
    public function validateSing(array $response): bool
    {
        $sign = $response['sign'];
        unset($response['sign']);
        return hash_equals($sign, $this->firmSecretKey($response));
    }

}
