<?php

namespace IngenicoClient;

use Ogone\DirectLink\PaymentOperation;

/**
 * Trait OpenInvoice
 * @package IngenicoClient
 */
trait OpenInvoice
{
    /**
     * Get Missing or Invalid Order's fields.
     *
     * @param mixed $orderId Order Id
     * @param PaymentMethod\PaymentMethod $pm PaymentMethod Instance
     * @param array $fields Order fields
     * @return array
     */
    public function getMissingOrderFields($orderId, PaymentMethod\PaymentMethod $pm, array $fields = [])
    {
        if (!in_array($pm->getId(), ['afterpay', 'klarna'])) {
            throw new Exception(sprintf('Unable to use "%s" as Open Invoice method.', $pm->getId()));
        }

        /** @var Order $order */
        $order = $this->getOrder($orderId);

        // Order items are required
        if (count((array) $order->getItems()) === 0) {
            $this->logger->debug(__CLASS__ . '::' . __METHOD__ . ' Open Invoice requires order items', [$order->getData(), $pm, $fields]);
            throw new Exception('Open Invoice requires order items');
        }

        // Get Order data and merge input
        $orderFields = array_merge($order->getData(), $fields);

        // Get Expected Fields
        $checkoutType = $order->getCheckoutType() ? $order->getCheckoutType() : PaymentMethod\PaymentMethod::CHECKOUT_B2C;
        $expectedFields = (array) $pm->getExpectedFields($checkoutType);

        // Get Missing or Invalid parameters
        $result = [];
        foreach ($expectedFields as $fieldName => $options) {
            // Check field value in session. Connector can use that
            $value = $this->getSessionValue('open_invoice_' . $orderId . '_' . $fieldName);
            if (!$value) {
                // Get field value of Order
                $value = $orderFields[$fieldName] ?? null;
            }

            $fieldObj = new OrderField($fieldName, $options);
            $fieldObj->setLabel($this->extension->getOrderFieldLabel($fieldName));
            $fieldObj->setValue($value);

            // Validate field
            try {
                $fieldObj->setIsValid(true);
                $fieldObj->setValidationMessage(null);
                $fieldObj->validate($value);
            } catch (Exception $e) {
                $fieldObj->setValidationMessage($this->__($e->getMessage()));
                $fieldObj->setIsValid(false);
                $result[] = $fieldObj;
            }

            // Translate values
            $values = $fieldObj->getValues();
            if (is_array($values) && count($values) > 0) {
                foreach ($values as $key1 => $value1) {
                    $values[$key1] = $this->__($value1);
                }

                $fieldObj->setValues($values);
            }
        }

        return $result;
    }

    /**
     * Validate Additional Fields.
     *
     * @param array $additionalFields
     * @param array $fields
     * @return array
     */
    public function validateAdditionalFields(array $additionalFields, array $fields = [])
    {
        // Check Open Invoice fields
        foreach ($additionalFields as &$field) {
            /** @var OrderField $field */
            $fieldName = $field->getFieldName();
            $value = $fields[$fieldName] ?? null;

            // Set Value
            $field->setValue($value);

            // Validate field
            try {
                $field->validate($value);
                $field->setIsValid(true);
                $field->setValidationMessage(null);
            } catch (\Exception $e) {
                $field->setValidationMessage($this->__($e->getMessage()));
                $field->setIsValid(false);
            }

            // Translate values
            $values = $field->getValues();
            if (is_array($values) && count($values) > 0) {
                foreach ($values as $key1 => $value1) {
                    $values[$key1] = $this->__($value1);
                }

                $field->setValues($values);
            }
        }

        return $additionalFields;
    }

    /**
     * Check if fields have invalid field.
     *
     * @param array $additionalFields
     * @return bool
     */
    public function haveInvalidAdditionalFields(array $additionalFields)
    {
        foreach ($additionalFields as &$field) {
            if (!$field->getIsValid()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Initiate Open Invoice Payment
     *
     * @param mixed $orderId Order Id
     * @param Alias $alias Alias Instance
     * @param array $fields Checkout Fields
     * @throws \Exception
     */
    public function initiateOpenInvoicePayment($orderId, $alias, array $fields = [])
    {
        if (!in_array($alias->getPaymentId(), ['afterpay', 'klarna'])) {
            throw new Exception(sprintf('Unable to use %s as Open Invoice method. Use %s::initiateRedirectPayment() instead of.', $alias->getPaymentId(), __CLASS__));
        }

        // Check Saved Order ID in Session
        $storedOrderId = $this->getSessionValue(IngenicoCoreLibrary::PARAM_NAME_OPEN_INVOICE_ORDER_ID);
        if ($storedOrderId && $storedOrderId !== $orderId) {
            // Destroy previous session data
            $this->unsetSessionValue(IngenicoCoreLibrary::PARAM_NAME_OPEN_INVOICE_ORDER_ID);
            $this->unsetSessionValue(IngenicoCoreLibrary::PARAM_NAME_OPEN_INVOICE_CHECKOUT_INPUT);
            $this->unsetSessionValue(IngenicoCoreLibrary::PARAM_NAME_OPEN_INVOICE_FIELDS);
        }

        // Store current Order ID
        $this->setSessionValue(IngenicoCoreLibrary::PARAM_NAME_OPEN_INVOICE_ORDER_ID, $orderId);

        $order = $this->getOrder($orderId);

        // Order items are required
        if (count((array) $order->getItems()) === 0) {
            $this->logger->debug(__CLASS__ . '::' . __METHOD__ . ' Open Invoice requires order items', [$order->getData(), $alias->getData(), $fields]);
            throw new Exception('Open Invoice requires order items');
        }

        // Save Order data in Session
        $this->setSessionValue(IngenicoCoreLibrary::PARAM_NAME_OPEN_INVOICE_CHECKOUT_INPUT, $fields);

        // Get Additional fields
        $additionalFields = $this->getSessionValue(IngenicoCoreLibrary::PARAM_NAME_OPEN_INVOICE_FIELDS);
        if (!$additionalFields) {
            $additionalFields = $this->getMissingOrderFields($orderId, $alias->getPaymentMethod(), $fields);

            // Save Additional Fields in Session
            $this->setSessionValue(IngenicoCoreLibrary::PARAM_NAME_OPEN_INVOICE_FIELDS, $additionalFields);
        }

        // Validate them
        $additionalFields = $this->validateAdditionalFields($additionalFields, $fields);

        // If needs clarification
        if (!$this->haveInvalidAdditionalFields($additionalFields)) {
            // Process Invalid fields, i.e. Show validation error
            $this->extension->clarifyOpenInvoiceAdditionalFields($orderId, $alias, $additionalFields);
            return;
        }

        // Clean up
        $this->unsetSessionValue(IngenicoCoreLibrary::PARAM_NAME_OPEN_INVOICE_ORDER_ID);
        $this->unsetSessionValue(IngenicoCoreLibrary::PARAM_NAME_OPEN_INVOICE_CHECKOUT_INPUT);
        $this->unsetSessionValue(IngenicoCoreLibrary::PARAM_NAME_OPEN_INVOICE_FIELDS);

        // Initiate OpenInvoice Payment with HostedCheckout API
        // @see IngenicoCoreLibrary::initiateRedirectPayment()
        if ($this->configuration->getSettingsDirectsales()) {
            $operation = new PaymentOperation(PaymentOperation::REQUEST_FOR_DIRECT_SALE);
        } else {
            $operation = new PaymentOperation(PaymentOperation::REQUEST_FOR_AUTHORISATION);
        }

        // Get order and add overridden fields
        $order = $this->getOrder($orderId);
        $order->setData($fields);

        // Redirect method require empty Alias name to generate new Alias
        if ($alias->getIsShouldStoredPermanently()) {
            $alias->setAlias('');
        }

        // Initiate HostedCheckout Payment Request
        $hostedCheckout = (new HostedCheckout())
            ->setConfiguration($this->configuration)
            ->setOrder($order)
            ->setUrls($this->requestReturnUrls($orderId))
            ->setPaymentMethod($alias->getPaymentMethod())
            ->setOperation($operation)
            ->setAlias($alias->getIsPreventStoring() ? null : $alias->exchange())
            ->setPm($alias->getPm())
            ->setBrand($alias->getBrand())
            ->setLanguage($order->getLocale())
            ->getPaymentRequest();

        $params = $hostedCheckout->toArray();
        $params['SHASIGN'] = $hostedCheckout->getShaSign();

        if ($this->logger) {
            $this->logger->debug(__CLASS__. '::' . __METHOD__, $params);
        }

        // Show page with list of payment methods
        $this->extension->showPaymentListRedirectTemplate([
            'order_id' => $orderId,
            'url' => $hostedCheckout->getOgoneUri(),
            'fields' => $params
        ]);
    }
}
