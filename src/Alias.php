<?php

namespace IngenicoClient;

/**
 * Class Alias
 * @method int getAliasId()
 * @method int getCustomerId()
 * @method $this setCustomerId($value)
 * @method string getAlias()
 * @method $this setAlias($value)
 * @method string getEd()
 * @method $this setEd($value)
 * @method string getBrand()
 * @method $this setBrand($value)
 * @method string getCardno()
 * @method $this setCardno($value)
 * @method string getBin()
 * @method $this setBin($value)
 * @method string getPm()
 * @method $this setPm($value)
 * @method string getOperation()
 * @method $this setOperation($operation)
 * @method string getUsage()
 * @method $this setUsage($usage)
 * @method string getIsShouldStoredPermanently()
 * @method $this setIsShouldStoredPermanently($value)
 * @method string getIsPreventStoring()
 * @method $this setIsPreventStoring($value)
 * @method string getForceSecurity()
 * @method $this setForceSecurity($value)
 *
 * @package IngenicoClient
 */
class Alias extends Data
{
    const OPERATION_BY_MERCHANT = 'BYMERCHANT';
    const OPERATION_BY_PSP = 'BYPSP';

    /**
     * Alias constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $data = array_change_key_case($data, CASE_LOWER);
        $this->setData($data);
    }

    /**
     * Pseudo for getAliasId()
     * @return mixed
     */
    public function getId()
    {
        return $this->getAliasId();
    }

    /**
     * Get Formatted Name
     * @return string
     */
    public function getName()
    {
        return sprintf('%s ends with %s, expires on %s/%s',
            $this->getBrand(),
            substr($this->getCardno(),-4,4),
            substr($this->getEd(), 0, 2),
            substr($this->getEd(), 2, 4)
        );
    }

    /**
     * Get Payment Method Instance
     * @return PaymentMethod\PaymentMethod
     * @throws Exception
     */
    public function getPaymentMethod()
    {
        $paymentMethods = new PaymentMethod();
        if ('Bancontact/Mister Cash' === $this->getBrand()) {
            $paymentMethod = $paymentMethods->getPaymentMethodByBrand('BCMC');
        } else {
            $paymentMethod = $paymentMethods->getPaymentMethodByBrand($this->getBrand());
        }

        if (!$paymentMethod) {
            throw new Exception('Can\'t to get PaymentMethod instance by Brand: ' . $this->getBrand());
        }

        return $paymentMethod;
    }

    /**
     * Get Logo
     * @return string
     */
    public function getEmbeddedLogo()
    {
        try {
            return $this->getPaymentMethod()->getEmbeddedLogo();
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Get Alias instance of SDK
     * @return \Ogone\Ecommerce\Alias
     */
    public function exchange()
    {
        return new \Ogone\Ecommerce\Alias($this->getAlias(), $this->getOperation(), $this->getUsage());
    }
}

