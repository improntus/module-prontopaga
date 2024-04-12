<?php

/**
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Improntus\ProntoPaga\Service;

use Improntus\ProntoPaga\Helper\Data as ProntoPagaHelper;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\SerializerInterface as Json;

class ProntoPagaApiService
{
    const URI_NEW_PAYMENT = 'api/payment/new';
    const URI_PAYMENT_METHODS = 'api/payment/methods/';
    const URI_PAYMENT_DETAILS = 'api/payment/data/';
    const URI_PAYMENT_REFUND = 'api/reverse/new';

    /**
     * @var ProntoPagaHelper
     */
    private $prontoPagaHelper;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var Json
     */
    private $json;

    /**
     * Constructor
     *
     * @param ProntoPagaHelper $prontoPagaHelper
     * @param Curl $curl
     * @param Json $json
     */
    public function __construct(
        ProntoPagaHelper $prontoPagaHelper,
        Curl $curl,
        Json $json
    ) {
        $this->prontoPagaHelper = $prontoPagaHelper;
        $this->curl = $curl;
        $this->json = $json;
    }

    /**
     * Create Payment request
     *
     * @param string $params
     * @return array|boolean|mixed
     */
    public function createPayment($params)
    {
        $url = $this->prontoPagaHelper->getApiEndpoint() . self::URI_NEW_PAYMENT;
        $params = $this->json->serialize($params);
        $result = $this->doRequest($url, 'POST', $params);

        if (!$result) {
            return false;
        }

        try {
            $result = $this->json->unserialize($result);
        } catch (\Exception $e) {
            $this->prontoPagaHelper->log(['type' => 'error', 'message' => $e->getMessage(), 'method' => __METHOD__]);
        }

        return  ['code' => $this->curl->getStatus(), 'body' => $result, 'request_body' => $params];
    }

    /**
     * Confirm Payment request
     *
     * @param string $params
     * @return array|boolean|mixed
     */
    public function confirmPayment($uid)
    {
        $url = $this->prontoPagaHelper->getApiEndpoint() . self::URI_PAYMENT_DETAILS . $uid;
        $result = $this->doRequest($url);

        if (!$result) {
            return false;
        }

        try {
            $result = $this->json->unserialize($result);
        } catch (\Exception $e) {
            $this->prontoPagaHelper->log(['type' => 'error', 'message' => $e->getMessage(), 'method' => __METHOD__]);
        }

        return  ['code' => $this->curl->getStatus(), 'body' => $result];
    }

    /**
     * Create Payment request
     *
     * @param string $params
     * @return array|boolean|mixed
     */
    public function createRefund($params)
    {
        $url = $this->prontoPagaHelper->getApiEndpoint() . self::URI_PAYMENT_REFUND;
        $params = $this->json->serialize($params);
        $result = $this->doRequest($url, 'POST', $params);

        if (!$result) {
            return false;
        }

        try {
            $result = $this->json->unserialize($result);
        } catch (\Exception $e) {
            $this->prontoPagaHelper->log(['type' => 'error', 'message' => $e->getMessage(), 'method' => __METHOD__]);
        }

        return  ['code' => $this->curl->getStatus(), 'body' => $result, 'request_body' => $params];
    }

    /**
     * Get all Payment methods by currency
     *
     * @param string $currency
     * @return array|boolean|mixed
     */
    public function getPaymentMethods(string $currency)
    {
        $url = $this->prontoPagaHelper->getApiEndpoint() . self::URI_PAYMENT_METHODS . $currency;
        $result = $this->doRequest($url);

        if (!$result) {
            return false;
        }

        $result = $this->json->unserialize($result);
        return  ['code' => $this->curl->getStatus(), 'methods' => $result];
    }


    /**
     * Make request
     *
     * @param string $url
     * @param string $action
     * @param mixed|null $params
     * @return mixed
     */
    function doRequest($url, $action = 'GET', $params = null)
    {
        $apiToken = $this->prontoPagaHelper->getApiToken();
        $headers = ["Authorization" => "Bearer $apiToken"];
        $this->curl->setHeaders($headers);

        if ($action == 'GET') {
            $this->curl->get($url);
        } elseif ($action == 'POST') {
            $this->curl->post($url, $params);
        }

        return $this->curl->getBody();
    }
}
