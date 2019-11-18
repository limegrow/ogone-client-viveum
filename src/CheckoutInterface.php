<?php

namespace IngenicoClient;

use Ogone\AbstractPaymentRequest;

/**
 * Interface ConnectorInterface.
 */
interface CheckoutInterface
{
    /**
     * Get Payment Request Instance
     *
     * @return AbstractPaymentRequest
     */
    public function getPaymentRequest();
}
