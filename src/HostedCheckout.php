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
        // Get Order
        $order = $this->getOrder();

        // Get Payment Method
        $paymentMethod = $this->getPaymentMethod();

        // Build Payment Request
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
            ->setLanguage($order->getLocale())
            ->setData($this->getData());

        if ($this->getAlias()) {
            $request->setAlias($this->getAlias());
        }

        // Set up templates
        switch ($this->getConfiguration()->getPaymentpageTemplate()) {
            case Configuration::PAYMENT_PAGE_TEMPLATE_INGENICO:
                $request->setTp($this->getConfiguration()->getPaymentpageTemplateName());
                break;
            case Configuration::PAYMENT_PAGE_TEMPLATE_STORE:
                $request->setTp($this->getConfiguration()->getRedirectPaymentPageTemplateUrl());
                break;
            case Configuration::PAYMENT_PAGE_TEMPLATE_EXTERNAL:
                $request->setTp($this->getConfiguration()->getPaymentpageTemplateExternalurl());
                break;
            default:
                // no break
        }

        /** @var EcommercePaymentRequest $request */
        $request = $this->assignOrder($request, $order, $paymentMethod);
        $request = $this->assignBrowserData($request, $order, $paymentMethod);

        // Parameters for Klarna
        if ($paymentMethod && $paymentMethod->getId() === 'klarna') {
            $request->setCuid($order->getCustomerRegistrationNumber())
                ->setCivility($order->getCustomerCivility())
                ->setEcomConsumerGender($order->getCustomerGender())
                ->setEcomShiptoPostalNamePrefix($order->getShippingCustomerTitle())
                ->setEcomShiptoDob($order->getCustomerDob())
                ->setEcomShiptoTelecomFaxNumber($order->getShippingFax())
                ->setEcomShiptoTelecomPhoneNumber($order->getShippingPhone())
                ->setEcomBilltoPostalStreetNumber($order->getBillingStreetNumber())
                ->setEcomShiptoPostalStreetNumber($order->getShippingStreetNumber())
                ->setEcomShiptoPostalState($order->getShippingState())
                ->setEcomBilltoPostalCounty($order->getBillingCountry())
                ->setEcomShiptoCounty($order->getShippingCountry())
                ->setOrdershipmeth($order->getShippingMethod())
                ->setOrdershipcost(bcmul($order->getShippingAmount() - $order->getShippingTaxAmount(), 100))
                ->setOrdershiptax(bcmul($order->getShippingTaxAmount(), 100))
                ->setOrdershiptaxcode($order->getShippingTaxCode() . '%')
                //->setEcomBilltoCompany($order->getCompanyName())
                ->setEcomShiptoCompany($order->getCompanyName())
                ->setEcomShiptoOnlineEmail($order->getShippingEmail())
                //->setEcomShiptoCounty($order->getShippingCountry())

                // Klarna doesn't support ECOM_BILLTO_POSTAL_STREET_LINE3
                ->unsEcomBilltoPostalStreetLine3()
                ->unsEcomShiptoPostalStreetLine3();
        }

        // Parameters for Afterpay
        if ($paymentMethod && $paymentMethod->getId() === 'afterpay') {
            // Check country
            if (!in_array($order->getBillingCountryCode(), ['DE', 'NL'])) {
                throw new \Exception(sprintf('This method doesn\'t support your country %s', $order->getBillingCountry()));
            }

            $request->setEcomShiptoPostalNamePrefix($order->getShippingCustomerTitle())
                ->setEcomShiptoOnlineEmail($order->getBillingEmail())
                ->setEcomBilltoPostalStreetNumber($order->getBillingStreetNumber())
                ->setEcomShiptoPostalStreetNumber($order->getShippingStreetNumber())
                ->setOrdershipmeth($order->getShippingMethod())
                ->setOrdershipcost(bcmul($order->getShippingAmount() - $order->getShippingTaxAmount(), 100))
                ->setOrdershiptax(bcmul($order->getShippingTaxAmount(), 100))
                ->setOrdershiptaxcode($order->getShippingTaxCode() . '%');

            $checkoutType = $order->getCheckoutType() ? $order->getCheckoutType() : Checkout::TYPE_B2C;
            if ($checkoutType === Checkout::TYPE_B2C) {
                // B2C
                $request->setEcomShiptoOnlineEmail($order->getBillingEmail())
                    ->setEcomConsumerGender($order->getCustomerGender())
                    ->setEcomShiptoDob($order->getCustomerDob())
                    ->setDatein($order->getShippingDateTime());
            } else {
                // B2B
                $request->setRefCustomerref($order->getRefCustomerref())
                    ->setEcomShiptoCompany($order->getCompanyName())
                    ->setEcomShiptoTva($order->getCompanyVat())
                    ->setRefCustomerid($order->getCustomerId())
                    ->setCostcenter($order->getCustomerId());
            }

            // Afterpay doesn't support ECOM_BILLTO_POSTAL_STREET_LINE3
            $request->unsEcomBilltoPostalStreetLine3()
                ->unsEcomShiptoPostalStreetLine3();
        }

        // Validate
        $request->validate();

        return $request;
    }

    /**
     * Request Hosted payment HTML.
     *
     * @deprecated Use getPaymentRequest() instead of
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
