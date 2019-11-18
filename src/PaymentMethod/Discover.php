<?php

namespace IngenicoClient\PaymentMethod;

class Discover extends PaymentMethod implements PaymentMethodInterface
{
    /**
     * ID Code
     * @var string
     */
    protected $id = 'discover';

    /**
     * Name
     * @var string
     */
    protected $name = 'Discover';

    /**
     * Logo
     * @var string
     */
    protected $logo = 'discover.svg';

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
    protected $brand = 'DISCOVER';

    /**
     * Countries
     * @var array
     */
    protected $countries = [
        'BE' => [
            'popularity' => 20
        ],
        'FR' => [
            'popularity' => 20
        ],
        'DE' => [
            'popularity' => 20
        ],
        'IT' => [
            'popularity' => 20
        ],
        'LU' => [
            'popularity' => 20
        ],
        'PT' => [
            'popularity' => 20
        ],
        'ES' => [
            'popularity' => 20
        ],
        'CH' => [
            'popularity' => 20
        ],
        'GB' => [
            'popularity' => 20
        ]
    ];
}
