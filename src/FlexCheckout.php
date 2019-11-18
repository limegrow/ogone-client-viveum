<?php

namespace IngenicoClient;

use Ogone\FlexCheckout\FlexCheckoutPaymentRequest;

/**
 * Class FlexCheckout
 *
 * @method $this setAlias(Alias $value)
 * @method Alias getAlias()
 * @method $this setTemplate($value)
 * @method mixed getTemplate()
 * @package IngenicoClient
 */
class FlexCheckout extends Checkout
{
    /**
     * Get Payment Request Instance
     *
     * @return FlexCheckoutPaymentRequest
     */
    public function getPaymentRequest()
    {
        $request = new FlexCheckoutPaymentRequest($this->getConfiguration()->getShaComposer('in'));

        // Set Production mode if enabled
        if (!$this->getConfiguration()->isTestMode()) {
            $request->setOgoneUri(FlexCheckoutPaymentRequest::PRODUCTION);
        }

        $request->setPspId($this->getConfiguration()->getPspid())
            ->setOrderId($this->getOrder()->getOrderId())
            ->setPaymentMethod($this->getAlias()->getPm())
            ->setBrand($this->getAlias()->getBrand())
            ->setAccepturl($this->getAcceptUrl())
            ->setExceptionurl($this->getExceptionUrl())
            ->setStorePermanently($this->getAlias()->getIsShouldStoredPermanently() ? 'Y' : 'N')
            ->setAliasId(new \Ogone\FlexCheckout\Alias($this->getAlias()->getAlias()))
            ->setTemplate($this->getTemplate())
            ->setLanguage($this->getOrder()->getLocale());

        return $request;
    }

    /**
     * Create FlexCheckout Payment Request
     *
     * @param Configuration $configuration
     * @param Order $order
     * @param Alias $alias
     * @param array $urls
     * @param string $template
     * @return FlexCheckoutPaymentRequest
     */
    public function createFlexCheckout(
        Configuration $configuration,
        Order $order,
        Alias $alias,
        $urls,
        $template
    ) {
        $request = (clone $this)
            ->setConfiguration($configuration)
            ->setOrder($order)
            ->setUrls($urls)
            ->setAlias($alias)
            ->setTemplate($template)
            ->getPaymentRequest();

        $request->setShaSign();
        $request->validate();

        return $request;
    }
}
