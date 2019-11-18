<?php

namespace IngenicoClient\PaymentMethod;

class DinersClub extends PaymentMethod implements PaymentMethodInterface
{
    /**
     * ID Code
     * @var string
     */
    protected $id = 'diners_club';

    /**
     * Name
     * @var string
     */
    protected $name = 'Diners Club';

    /**
     * Logo
     * @var string
     */
    protected $logo = 'diners_club.svg';

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
    protected $brand = 'Diners Club';

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
