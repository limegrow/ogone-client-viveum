<?php

namespace IngenicoClient;

use Ogone\DirectLink\Alias;
use Ogone\DirectLink\DirectLinkPaymentRequest;
use Ogone\DirectLink\DirectLinkPaymentResponse;
use Ogone\DirectLink\DirectLinkQueryRequest;
use Ogone\DirectLink\DirectLinkQueryResponse;
use Ogone\DirectLink\DirectLinkMaintenanceRequest;
use Ogone\DirectLink\DirectLinkMaintenanceResponse;
use Ogone\DirectLink\MaintenanceOperation;
use Ogone\DirectLink\PaymentOperation;

/**
 * Class DirectLink
 *
 * @method $this setAlias(Alias $value)
 * @method Alias getAlias()
 * @method $this setCvc($value)
 * @method mixed getCvc()
 * @package IngenicoClient
 */
class DirectLink extends Checkout implements CheckoutInterface
{
    /**
     * Get Payment Request Instance
     *
     * @return DirectLinkPaymentRequest
     */
    public function getPaymentRequest()
    {
        $request = new DirectLinkPaymentRequest($this->getConfiguration()->getShaComposer('in'));

        // Set Production mode if enabled
        if (!$this->getConfiguration()->isTestMode()) {
            $request->setOgoneUri(DirectLinkPaymentRequest::PRODUCTION);
        }

        $request->setOrig($this->getConfiguration()->getShoppingCartExtensionId())
            ->setShoppingCartExtensionId($this->getConfiguration()->getShoppingCartExtensionId())
            ->setPspId($this->getConfiguration()->getPspid())
            ->setUserId($this->getConfiguration()->getUserId())
            ->setPassword($this->getConfiguration()->getPassword())
            ->setAccepturl($this->getAcceptUrl())
            ->setDeclineurl($this->getDeclineUrl())
            ->setExceptionurl($this->getExceptionUrl())
            ->setCancelurl($this->getCancelUrl())
            ->setBackurl($this->getBackUrl())
            ->setAmount($this->getOrder()->getAmountInCents())
            ->setCurrency($this->getOrder()->getCurrency())
            ->setLanguage($this->getOrder()->getLocale())
            ->setAlias($this->getAlias())
            ->setEci($this->getEci())
            ->setCreditDebit($this->getCreditDebit())
            ->setData($this->getData());

        // Add Order values
        $request = $this->assignOrder($request, $this->getOrder());

        // Use 3DSecure
        if ($this->getIsSecure()) {
            // MPI 2.0 (3DS V.2)
            $request->setFlag3D('Y')
                ->setHttpAccept($this->getOrder()->getHttpAccept())
                ->setHttpUserAgent($this->getOrder()->getHttpUserAgent())
                ->setWin3DS(self::WIN3DS_MAIN)
                ->setComplus($this->getOrder()->getOrderId());

            // Add Browser values
            $request = $this->assignBrowserData($request, $this->getOrder());
        }

        $this->unsData('is_secure');
        $request->validate();

        return $request;
    }

    /**
     * Create Direct Link payment request.
     *
     * @param Configuration $configuration
     * @param Order         $order
     * @param \Ogone\Ecommerce\Alias $alias
     * @param PaymentOperation $operation
     * @param array            $urls
     * @param string|null      $cvc
     *
     * @return Payment
     * @SuppressWarnings("Duplicates")
     */
    public function createDirectLinkRequest(
        Configuration $configuration,
        Order $order,
        \Ogone\Ecommerce\Alias $alias,
        $operation,
        array $urls,
        $cvc = null
    ) {
        /** @var DirectLinkPaymentRequest $request */
        $request = (clone $this);

        $request->setConfiguration($configuration)
            ->setOrder($order)
            ->setUrls($urls)
            ->setOperation($operation)
            ->setAlias((new Alias($alias->getAlias()))->setAliasOperation($alias->getAliasOperation()))
            ->setCvc($cvc);

        $dlPaymentRequest = $request->getPaymentRequest();

        $client = new Client($this->logger);
        $response = $client->post(
            $dlPaymentRequest->toArray(),
            $dlPaymentRequest->getOgoneUri(),
            $dlPaymentRequest->getShaSign()
        );

        return new Payment((new DirectLinkPaymentResponse($response))->toArray());
    }

    /**
     * Create Refund Request.
     *
     * @param Configuration $configuration
     * @param string        $orderId
     * @param string        $payId
     * @param int           $amount
     * @param bool          $isPartially
     *
     * @return Payment
     */
    public function createRefund(
        Configuration $configuration,
        $orderId,
        $payId,
        $amount,
        $isPartially
    ) {
        $operation = $isPartially ? MaintenanceOperation::OPERATION_REFUND_PARTIAL : MaintenanceOperation::OPERATION_REFUND_LAST_OR_FULL;
        return $this->createMaintenanceRequest($configuration, $orderId, $payId, $amount, [], new MaintenanceOperation($operation));
    }

    /**
     * Create Capture Request.
     *
     * @param Configuration $configuration
     * @param string        $orderId
     * @param string        $payId
     * @param int           $amount
     * @param bool          $isPartially
     *
     * @return Payment
     */
    public function createCapture(
        Configuration $configuration,
        $orderId,
        $payId,
        $amount,
        $isPartially
    ) {
        $operation = $isPartially ? MaintenanceOperation::OPERATION_CAPTURE_PARTIAL : MaintenanceOperation::OPERATION_CAPTURE_LAST_OR_FULL;
        return $this->createMaintenanceRequest($configuration, $orderId, $payId, $amount, [], new MaintenanceOperation($operation));
    }

    /**
     * Create Void Request.
     *
     * @param Configuration $configuration
     * @param string        $orderId
     * @param string        $payId
     * @param int           $amount
     * @param bool          $isPartially
     *
     * @return Payment
     */
    public function createVoid(
        Configuration $configuration,
        $orderId,
        $payId,
        $amount,
        $isPartially
    ) {
        $operation = $isPartially ? MaintenanceOperation::OPERATION_AUTHORISATION_DELETE : MaintenanceOperation::OPERATION_AUTHORISATION_DELETE_AND_CLOSE;
        return $this->createMaintenanceRequest($configuration, $orderId, $payId, $amount, [], new MaintenanceOperation($operation));
    }

    /**
     * Create Maintenance Request.
     *
     * Items array should contain item with keys like:
     * ['itemid', 'itemname', 'itemprice', 'itemquant', 'itemvatcode', 'taxincluded']
     *
     * @param Configuration        $configuration
     * @param string               $orderId
     * @param string               $payId
     * @param int                  $amount
     * @param array                $items
     * @param MaintenanceOperation $operation
     *
     * @return Payment
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings("cognitive-complexity")
     */
    public function createMaintenanceRequest(
        Configuration $configuration,
        $orderId,
        $payId,
        $amount,
        array $items,
        MaintenanceOperation $operation
    ) {
        $maintenanceRequest = new DirectLinkMaintenanceRequest($configuration->getShaComposer());

        // Set Production mode if enabled
        if (!$configuration->isTestMode()) {
            $maintenanceRequest->setOgoneUri(DirectLinkMaintenanceRequest::PRODUCTION);
        }

        $maintenanceRequest->setPspId($configuration->getPspid());
        $maintenanceRequest->setUserId($configuration->getUserId());
        $maintenanceRequest->setPassword($configuration->getPassword());
        $maintenanceRequest->setOperation($operation);

        if ($orderId) {
            $maintenanceRequest->setOrderId($orderId);
        }

        if ($payId) {
            $maintenanceRequest->setPayId($payId);
        }

        if ($amount > 0) {
            $maintenanceRequest->setAmount((int) bcmul(100, $amount));
        }

        if (count($items) > 0) {
            foreach ($items as &$item) {
                if (isset($item[self::ITEM_ID])) {
                    $item[self::ITEM_ID] = mb_strimwidth($item[self::ITEM_ID], 0, 15);
                }

                if (isset($item[self::ITEM_NAME])) {
                    $item[self::ITEM_NAME] = mb_strimwidth($item[self::ITEM_NAME], 0, 30);
                }

                if (isset($item[self::ITEM_PRICE])) {
                    $item[self::ITEM_PRICE] = (int) bcmul(100, $item[self::ITEM_PRICE]);
                }

                if (isset($item[self::ITEM_VATCODE])) {
                    $item[self::ITEM_VATCODE] = $item[self::ITEM_VATCODE] . '%';
                }
            }

            $maintenanceRequest->setItems($items);
        }

        $params = $maintenanceRequest->toArray();
        $url = $maintenanceRequest->getOgoneUri();
        $shaSign = $maintenanceRequest->getShaSign();

        $client = new Client($this->logger);
        $response = $client->post($params, $url, $shaSign);

        return new Payment((new DirectLinkMaintenanceResponse($response))->toArray());
    }

    /**
     * Create payment status request.
     *
     * @param Configuration $configuration
     * @param $orderId
     * @param $payId
     *
     * @return Payment
     */
    public function createStatusRequest(
        Configuration $configuration,
        $orderId,
        $payId
    ) {
        $queryRequest = new DirectLinkQueryRequest($configuration->getShaComposer());

        // Set Production mode if enabled
        if (!$configuration->isTestMode()) {
            $queryRequest->setOgoneUri(DirectLinkQueryRequest::PRODUCTION);
        }

        $queryRequest->setPspId($configuration->getPspid());
        $queryRequest->setUserId($configuration->getUserId());
        $queryRequest->setPassword($configuration->getPassword());

        if ($orderId) {
            $queryRequest->setOrderId($orderId);
        }

        if ($payId) {
            $queryRequest->setPayId($payId);
        }

        $params = $queryRequest->toArray();
        $url = $queryRequest->getOgoneUri();
        $shaSign = $queryRequest->getShaSign();

        $client = new Client($this->logger);
        $response = $client->post($params, $url, $shaSign);
        return new Payment((new DirectLinkQueryResponse($response))->toArray());
    }
}
