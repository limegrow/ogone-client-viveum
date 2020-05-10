<?php

namespace IngenicoClient;

interface OpenInvoiceInterface
{
    /**
     * Get Missing or Invalid Order's fields.
     *
     * @param mixed $orderId Order Id
     * @param PaymentMethod\PaymentMethod $pm PaymentMethod Instance
     * @param array $fields Order fields
     * @return array
     */
    public function getMissingOrderFields($orderId, PaymentMethod\PaymentMethod $pm, array $fields = []);

    /**
     * Validate Additional Fields.
     *
     * @param array $additionalFields
     * @param array $fields
     * @return array
     */
    public function validateAdditionalFields(array $additionalFields, array $fields = []);

    /**
     * Check if fields have invalid field.
     *
     * @param array $additionalFields
     * @return bool
     */
    public function haveInvalidAdditionalFields(array $additionalFields);

    /**
     * Initiate Open Invoice Payment
     *
     * @param mixed $orderId
     * @param Alias $alias
     * @param array $fields
     * @throws \Exception
     */
    public function initiateOpenInvoicePayment($orderId, $alias, array $fields = []);
}
