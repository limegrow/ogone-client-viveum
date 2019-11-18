<?php

namespace IngenicoClient\PaymentMethod;

class BankTransfer extends PaymentMethod implements PaymentMethodInterface
{
    /**
     * ID Code
     * @var string
     */
    protected $id = 'bank_transfer';

    /**
     * Name
     * @var string
     */
    protected $name = 'Bank transfer';

    /**
     * Logo
     * @var string
     */
    protected $logo = 'bank_transfer.svg';

    /**
     * Category
     * @var string
     */
    protected $category = 'real_time_banking';

    /**
     * Payment Method
     * @var string
     */
    protected $pm = 'Bank transfer';

    /**
     * Brand
     * @var string
     */
    protected $brand = 'Bank transfer';

    /**
     * Countries
     * @var array
     */
    protected $countries = [
        'BE' => [
            'popularity' => 40
        ],
        'DE' => [
            'popularity' => 40
        ],
        'FR' => [
            'popularity' => 40
        ],
        'NL' => [
            'popularity' => 40
        ]
    ];

    /**
     * Is support Redirect only
     * @var bool
     */
    protected $is_redirect_only = true;

    /**
     * Transaction codes that indicate capturing.
     * @var array
     */
    protected $direct_sales_success_code = [41];

    /**
     * Transaction codes that indicate authorization.
     * @var array
     */
    protected $auth_mode_success_code = [];
}
