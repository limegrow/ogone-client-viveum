<?php

namespace IngenicoClient\PaymentMethod;

class Ideal extends PaymentMethod implements PaymentMethodInterface
{
    /**
     * ID Code
     * @var string
     */
    protected $id = 'ideal';

    /**
     * Name
     * @var string
     */
    protected $name = 'iDEAL';

    /**
     * Logo
     * @var string
     */
    protected $logo = 'ideal.svg';

    /**
     * Category
     * @var string
     */
    protected $category = 'real_time_banking';

    /**
     * Payment Method
     * @var string
     */
    protected $pm = 'iDEAL';

    /**
     * Brand
     * @var string
     */
    protected $brand = 'iDEAL';

    /**
     * Countries
     * @var array
     */
    protected $countries = [
        'NL' => [
            'popularity' => 100
        ]
    ];

    /**
     * Is support Redirect only
     * @var bool
     */
    protected $is_redirect_only = true;
}
