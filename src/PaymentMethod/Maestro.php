<?php

namespace IngenicoClient\PaymentMethod;

class Maestro extends PaymentMethod implements PaymentMethodInterface
{
    /**
     * ID Code
     * @var string
     */
    protected $id = 'maestro';

    /**
     * Name
     * @var string
     */
    protected $name = 'Maestro';

    /**
     * Logo
     * @var string
     */
    protected $logo = 'maestro.svg';

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
    protected $brand = 'Maestro';

    /**
     * Countries
     * @var array
     */
    protected $countries = [
        'BE' => [
            'popularity' => 40
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
        'GB' => [
            'popularity' => 20
        ]
    ];
}
