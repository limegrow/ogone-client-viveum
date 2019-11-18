<?php

namespace IngenicoClient;

use Ogone\Ecommerce\Alias;
use Ogone\Ecommerce\EcommercePaymentRequest;
use Ogone\Ecommerce\EcommercePaymentResponse;
use Ogone\DirectLink\PaymentOperation;

/**
 * Class HostedCheckout
 *
 * @method $this setAlias(Alias $value)
 * @method Alias getAlias()
 * @method $this setPm($value)
 * @method mixed getPm()
 * @method $this setBrand($value)
 * @method mixed getBrand()
 * @package IngenicoClient
 */
class HostedCheckout extends Checkout implements CheckoutInterface
{
    /**
     * Get Payment Request Instance
     *
     * @return EcommercePaymentRequest
     */
    public function getPaymentRequest()
    {
        $request = new EcommercePaymentRequest($this->getConfiguration()->getShaComposer('in'));

        // Set Production mode if enabled
        if (!$this->getConfiguration()->isTestMode()) {
            $request->setOgoneUri(EcommercePaymentRequest::PRODUCTION);
        }

        $request->setOrig($this->getConfiguration()->getShoppingCartExtensionId())
            ->setShoppingCartExtensionId($this->getConfiguration()->getShoppingCartExtensionId())
            ->setPspId($this->getConfiguration()->getPspid())
            ->setAccepturl($this->getAcceptUrl())
            ->setDeclineurl($this->getDeclineUrl())
            ->setExceptionurl($this->getExceptionUrl())
            ->setCancelurl($this->getCancelUrl())
            ->setBackurl($this->getBackUrl())
            ->setLanguage($this->getOrder()->getLocale())
            ->setData($this->getData());

        if ($this->getAlias()) {
            $request->setAlias($this->getAlias());
        }

        /** @var EcommercePaymentRequest $request */
        $request = $this->assignOrder($request, $this->getOrder());
        $request = $this->assignBrowserData($request, $this->getOrder());

        // Validate
        $request->validate();

        return $request;
    }

    /**
     * Request Hosted payment HTML.
     *
     * @param Configuration    $configuration
     * @param Order            $order
     * @param array            $urls
     * @param PaymentOperation $operation
     * @param Alias $alias
     * @param $pm
     * @param $brand
     *
     * @return Data
     * @SuppressWarnings("Duplicates")
     */
    public function createHostedCheckout(
        Configuration $configuration,
        Order $order,
        array $urls,
        $operation = null,
        Alias $alias = null,
        $pm = null,
        $brand = null
    ) {
        $request = (clone $this)
            ->setConfiguration($configuration)
            ->setOrder($order)
            ->setUrls($urls)
            ->setOperation($operation)
            ->setAlias($alias)
            ->setPm($pm)
            ->setBrand($brand)
            ->setLanguage($order->getLocale())
            ->getPaymentRequest();

        $params = $request->toArray();
        $params['SHASIGN'] = $request->getShaSign();

        if ($this->logger) {
            $this->logger->debug(__CLASS__. '::' . __METHOD__, $params);
        }

        $result = new Data();
        $result->setUrl($request->getOgoneUri())
            ->setFields($params);

        return $result;
    }

    /**
     * Validate Hosted payment return request.
     *
     * @param Configuration $configuration
     * @param $request
     *
     * @return mixed
     */
    public function validate(Configuration $configuration, $request)
    {
        $ecommercePaymentResponse = new EcommercePaymentResponse($request);

        return $ecommercePaymentResponse->isValid($configuration->getShaComposer('out'));
    }
}
