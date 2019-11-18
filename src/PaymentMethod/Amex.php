<?php

namespace IngenicoClient\PaymentMethod;

class Amex extends PaymentMethod implements PaymentMethodInterface
{
    /**
     * ID Code
     * @var string
     */
    protected $id = 'amex';

    /**
     * Name
     * @var string
     */
    protected $name = 'American Express';

    /**
     * Logo
     * @var string
     */
    protected $logo = 'amex.svg';

    /**
     * Category
     * @var string
     */
    protected $category = 'card';

    /**
     * Payment Method
     * @var string
     */
    protected $pm = 'CreditCard';

    /**
     * Brand
     * @var string
     */
    protected $brand = 'American Express';

    /**
     * Countries
     * @var array
     */
    protected $countries = [
        'BE' => [
            'popularity' => 40
        ],
        'FR' => [
            'popularity' => 40
        ],
        'DE' => [
            'popularity' => 40
        ],
        'IT' => [
            'popularity' => 40
        ],
        'LU' => [
            'popularity' => 20
        ],
        'NL' => [
            'popularity' => 40
        ],
        'PT' => [
            'popularity' => 40
        ],
        'ES' => [
            'popularity' => 40
        ],
        'CH' => [
            'popularity' => 40
        ],
        'GB' => [
            'popularity' => 40
        ]
    ];

    /**
     * Is Security Mandatory
     * @var bool
     */
    protected $is_security_mandatory = false;
}
