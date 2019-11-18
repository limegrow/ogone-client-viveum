<?php

namespace IngenicoClient\PaymentMethod;

use IngenicoClient\Exception;

/**
 * Class PaymentMethod
 * @method mixed getIFrameUrl()
 * @method $this setIFrameUrl($url)
 * @package IngenicoClient\PaymentMethod
 */
abstract class PaymentMethod  implements \ArrayAccess, PaymentMethodInterface
{
    /**
     * ID Code
     * @var string
     */
    protected $id;

    /**
     * Name
     * @var string
     */
    protected $name;

    /**
     * Logo
     * @var string
     */
    protected $logo;

    /**
     * Category
     * @var string
     */
    protected $category;

    /**
     * Category Name
     * @var string
     */
    protected $category_name;

    /**
     * Payment Method
     * @var string
     */
    protected $pm;

    /**
     * Brand
     * @var string
     */
    protected $brand;

    /**
     * Countries
     * @var array
     */
    protected $countries;

    /**
     * Is Security Mandatory
     * @var bool
     */
    protected $is_security_mandatory = false;

    /**
     * Credit Debit Flag (C or D)
     * @var string
     */
    protected $credit_debit;

    /**
     * Is support Redirect only
     * @var bool
     */
    protected $is_redirect_only = false;

    /**
     * Is support Two phase flow
     * @var bool
     */
    protected $two_phase_flow = true;

    /**
     * Transaction codes that indicate capturing.
     * @var array
     */
    protected $direct_sales_success_code = [9];

    /**
     * Transaction codes that indicate authorization.
     * @var array
     */
    protected $auth_mode_success_code = [5];

    /**
     * PaymentMethod constructor.
     * @param array|null $data
     */
    public function __construct($data = null)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    /**
     * Get ID
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get Name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get Category
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Get Category Name
     * @return string
     */
    public function getCategoryName()
    {
        return $this->category_name;
    }

    /**
     * Get PM
     * @return string
     */
    public function getPM()
    {
        return $this->pm;
    }

    /**
     * Get Brand
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Get Countries
     * @return array
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * Is Security Mandatory
     * @return bool
     */
    public function isSecurityMandatory()
    {
        return $this->is_security_mandatory;
    }

    /**
     * Get Credit Debit Flag
     * @return string
     */
    public function getCreditDebit()
    {
        return $this->credit_debit;
    }

    /**
     * Is support Redirect only
     * @return bool
     */
    public function isRedirectOnly()
    {
        return $this->is_redirect_only;
    }

    /**
     * Returns codes that indicate capturing.
     * @return array
     */
    public function getDirectSalesSuccessCode()
    {
        return $this->direct_sales_success_code;
    }

    /**
     * Returns codes that indicate authorization.
     * @return array
     */
    public function getAuthModeSuccessCode()
    {
        return $this->auth_mode_success_code;
    }

    /**
     * Is support Two Phase Flow
     * @return bool
     */
    public function isTwoPhaseFlow()
    {
        return $this->two_phase_flow;
    }

    /**
     * Get Logo
     * @return string
     */
    public function getEmbeddedLogo()
    {
        $file = realpath(__DIR__ . '/../../assets/images/payment_logos/' . $this->logo);
        if (file_exists($file)) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $mime = mime_content_type($file);

            if ('svg' === $extension) {
                $mime = 'image/svg+xml';
            }

            if (strpos($mime, 'image') !== false) {
                $contents = file_get_contents($file);
                return sprintf('data:%s;base64,%s', $mime, base64_encode($contents));
            }
        }

        return '';
    }

    /**
     * Get object data by key with calling getter method
     *
     * @param string $key
     * @param mixed $args
     * @return mixed
     */
    public function getDataUsingMethod($key, $args = null)
    {
        $method = 'get' . $this->_camelize($key);
        return $this->$method($args);
    }

    /**
     * Get data
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * Set/Get attribute wrapper
     *
     * @param   string $method
     * @param   array $args
     * @return  mixed
     */
    public function __call($method, $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get' :
                $key = $this->_underscore(substr($method,3));
                return property_exists($this, $key) ? $this->$key : null;
            case 'set' :
                $key = $this->_underscore(substr($method,3));
                $this->$key = isset($args[0]) ? $args[0] : null;
                return $this;
            case 'uns' :
                $key = $this->_underscore(substr($method,3));
                unset($this->$key);
                return $this;
            case 'has' :
                $key = $this->_underscore(substr($method,3));
                return property_exists($this, $key);
        }

        throw new Exception(sprintf('Invalid method %s::%s', get_class($this), $method));
    }

    /**
     * Implementation of \ArrayAccess::offsetSet()
     *
     * @param string $offset
     * @param mixed $value
     * @return void
     * @link http://www.php.net/manual/en/arrayaccess.offsetset.php
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * Implementation of \ArrayAccess::offsetExists()
     *
     * @param string $offset
     * @return bool
     * @link http://www.php.net/manual/en/arrayaccess.offsetexists.php
     */
    public function offsetExists($offset)
    {
        return property_exists($this, $offset);
    }

    /**
     * Implementation of \ArrayAccess::offsetUnset()
     *
     * @param string $offset
     * @return void
     * @link http://www.php.net/manual/en/arrayaccess.offsetunset.php
     */
    public function offsetUnset($offset)
    {
        if (property_exists($this, $offset)) {
            unset($this->$offset);
        }
    }

    /**
     * Implementation of \ArrayAccess::offsetGet()
     *
     * @param string $offset
     * @return mixed
     * @link http://www.php.net/manual/en/arrayaccess.offsetget.php
     */
    public function offsetGet($offset)
    {
        if (property_exists($this, $offset)) {
            return $this->$offset;
        }

        return null;
    }

    /**
     * Converts field names for setters and getters
     *
     * @param string $name
     * @return string
     */
    protected function _underscore($name)
    {
        $result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
        return $result;
    }

    /**
     * Camelize string
     * Example: super_string to superString
     *
     * @param $name
     * @return string
     */
    protected function _camelize($name)
    {
        return $this->uc_words($name, '');
    }

    /**
     * Tiny function to enhance functionality of ucwords
     *
     * Will capitalize first letters and convert separators if needed
     *
     * @param string $str
     * @param string $destSep
     * @param string $srcSep
     * @return string
     */
    protected function uc_words($str, $destSep='_', $srcSep='_')
    {
        return str_replace(' ', $destSep, ucwords(str_replace($srcSep, ' ', $str)));
    }
}

