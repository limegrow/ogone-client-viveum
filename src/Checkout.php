<?php

namespace IngenicoClient;

use IngenicoClient\PaymentMethod\PaymentMethod;
use Ogone\DirectLink\PaymentOperation;
use Psr\Log\LoggerInterface;
use Ogone\AbstractPaymentRequest;
use Ogone\DirectLink\Eci;

/**
 * Class Checkout
 *
 * @method $this setCreditDebit($value)
 * @method mixed getCreditDebit()
 * @method $this setIsSecure($value)
 * @method mixed getIsSecure()
 * @method $this setLanguage($value)
 * @method mixed getLanguage()
 * @method $this setEci(Eci $value)
 * @method Eci|null getEci()
 * @method $this setOperation(PaymentOperation $value)
 * @method PaymentOperation|null getOperation()
 * @method $this setAlias($value)
 * @method mixed getAlias()
 * @package IngenicoClient
 */
abstract class Checkout extends Data implements CheckoutInterface
{
    const WIN3DS_MAIN = 'MAINW';
    const WIN3DS_POPUP = 'POPUP';
    const WIN3DS_POPIX = 'POPIX';

    /**
     * Checkout types
     */
    const TYPE_B2C = 'b2c';
    const TYPE_B2B = 'b2b';

    const ITEM_ID = 'itemid';
    const ITEM_NAME = 'itemname';
    const ITEM_PRICE = 'itemprice';
    const ITEM_VATCODE = 'itemvatcode';

    /** @var LoggerInterface|null */
    protected $logger;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var PaymentMethod
     */
    protected $payment_method;

    /**
     * @var array
     */
    protected $urls = [
        'accept',
        'decline',
        'exception',
        'cancel',
        'back'
    ];

    /**
     * Checkout constructor.
     */
    public function __construct()
    {
        // Default values
        $this->setIsSecure(false);
        $this->setLanguage('en_US');
    }

    /**
     * Set Logger.
     *
     * @param LoggerInterface|null $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Get Logger.
     *
     * @return LoggerInterface|null
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Set Configuration
     *
     * @param Configuration $configuration
     * @return $this
     */
    public function setConfiguration(Configuration $configuration)
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * Get Configuration
     *
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Set Order
     *
     * @param Order $order
     * @return $this
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get Order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set Payment Method
     *
     * @param PaymentMethod $paymentMethod
     * @return $this
     */
    public function setPaymentMethod(PaymentMethod $paymentMethod)
    {
        $this->payment_method = $paymentMethod;

        return $this;
    }

    /**
     * Get Payment Method
     *
     * @return PaymentMethod
     */
    public function getPaymentMethod()
    {
        return $this->payment_method;
    }

    /**
     * Set Urls
     *
     * @param array $urls
     * @return $this
     */
    public function setUrls(array $urls)
    {
        $this->urls = $urls;

        return $this;
    }

    /**
     * Get Urls
     *
     * @return array
     */
    public function getUrls()
    {
        return $this->urls;
    }

    /**
     * Set Accept Url
     *
     * @param $url
     * @return $this
     */
    public function setAcceptUrl($url)
    {
        $this->urls['accept'] = $url;

        return $this;
    }

    /**
     * Get Accept Url
     *
     * @return string
     */
    public function getAcceptUrl()
    {
        return $this->urls['accept'];
    }

    /**
     * Set Decline Url
     *
     * @param $url
     * @return $this
     */
    public function setDeclineUrl($url)
    {
        $this->urls['decline'] = $url;

        return $this;
    }

    /**
     * Get Decline Url
     *
     * @return string
     */
    public function getDeclineUrl()
    {
        return $this->urls['decline'];
    }

    /**
     * Set Exception Url
     *
     * @param $url
     * @return $this
     */
    public function setExceptionUrl($url)
    {
        $this->urls['exception'] = $url;

        return $this;
    }

    /**
     * Get Exception Url
     *
     * @return string
     */
    public function getExceptionUrl()
    {
        return $this->urls['exception'];
    }

    /**
     * Set Cancel Url
     *
     * @param $url
     * @return $this
     */
    public function setCancelUrl($url)
    {
        $this->urls['cancel'] = $url;

        return $this;
    }

    /**
     * Get Cancel Url
     *
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->urls['cancel'];
    }

    /**
     * Set Back Url
     *
     * @param $url
     * @return $this
     */
    public function setBackUrl($url)
    {
        $this->urls['back'] = $url;

        return $this;
    }

    /**
     * Get Back Url
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->urls['back'];
    }

    /**
     * Assign Order
     *
     * @param AbstractPaymentRequest $request
     * @param Order $order
     * @param PaymentMethod|null $paymentMethod
     * @return AbstractPaymentRequest
     */
    protected function assignOrder(AbstractPaymentRequest $request, Order $order, $paymentMethod = null)
    {
        // Set values for Request instance
        $request->setOrderId($order->getOrderId())
            ->setAmount($order->getAmountInCents())
            ->setCurrency($order->getCurrency())
            ->setOwnerAddress($order->getBillingAddress1())
            ->setOwnercty($order->getBillingCountryCode())
            ->setOwnerTown($order->getBillingCity())
            ->setOwnerZip($order->getBillingPostcode())
            ->setOwnertelno($order->getBillingPhone())
            ->setCivility($order->getCustomerCivility())
            ->setCn($order->getBillingFullName())
            ->setEmail($order->getBillingEmail())
            ->setCuid($order->getBillingEmail())
            ->setEcomShiptoDob($order->getCustomerDob())
            ->setRemoteAddr($order->getCustomerIp())
            ->setAddrmatch($order->getIsShippingSame() ? '1' : '0')
            ->setEcomBilltoPostalNameFirst($order->getBillingFirstName())
            ->setEcomBilltoPostalNameLast($order->getBillingLastName())
            ->setEcomBilltoPostalCountrycode($order->getBillingCountryCode())
            ->setEcomBilltoPostalCity($order->getBillingCity())
            ->setEcomBilltoPostalPostalcode($order->getBillingPostcode())
            ->setEcomBilltoPostalStreetLine1($order->getBillingAddress1())
            ->setEcomBilltoPostalStreetLine2($order->getBillingAddress2())
            //->setEcomBilltoPostalStreetLine3($order->getBillingAddress3())
            ->setEcomShiptoPostalNameFirst($order->getShippingFirstName())
            ->setEcomShiptoPostalNameLast($order->getShippingLastName())
            ->setEcomShiptoPostalCountrycode($order->getShippingCountryCode())
            ->setEcomShiptoPostalCity($order->getShippingCity())
            ->setEcomShiptoPostalPostalcode($order->getShippingPostcode())
            ->setEcomShiptoPostalStreetLine1($order->getShippingAddress1())
            ->setEcomShiptoPostalStreetLine2($order->getShippingAddress2());
            //->setEcomShiptoPostalStreetLine3($order->getShippingAddress3());

        if ($paymentMethod) {
            // Generating the string with the list of items for the PMs that are requiring it (i.e. Open Invoice)
            if ($paymentMethod->getOrderLineItemsRequired() && $items = (array) $order->getItems()) {
                /** @var OrderItem $item */
                foreach ($items as $id => $item) {
                    // Don't pass shipping item for Klarna/Afterpay. It uses Ordershipcost instead of.
                    if ($paymentMethod && in_array($paymentMethod->getId(), ['klarna', 'afterpay'])) {
                        if ($item->getType() === OrderItem::TYPE_SHIPPING) {
                            continue;
                        }
                    }

                    $fields = $item->exchange();

                    foreach ($fields as $key => $value) {
                        $request->setData($key . ($id + 1), $value);
                    }
                }
            }
        }

        return $request;
    }

    /**
     * Assign Browser data from Cookies.
     *
     * There are follow cookies:
     * 'browserColorDepth',
     * 'browserJavaEnabled',
     * 'browserLanguage',
     * 'browserScreenHeight',
     * 'browserScreenWidth',
     * 'browserTimeZone'
     *
     * @param AbstractPaymentRequest $request
     * @param Order $order
     * @param PaymentMethod|null $paymentMethod
     * @return AbstractPaymentRequest
     */
    public function assignBrowserData(AbstractPaymentRequest $request, Order $order, $paymentMethod = null)
    {
        $request->setBrowseracceptheader($order->getHttpAccept());
        $request->setBrowseruseragent($order->getHttpUserAgent());

        // Add Browser values
        $browserValues = [
            'browserColorDepth',
            'browserJavaEnabled',
            'browserLanguage',
            'browserScreenHeight',
            'browserScreenWidth',
            'browserTimeZone'
        ];

        foreach ($browserValues as $key) {
            if (isset($_COOKIE[$key])) {
                $request->setData(strtolower($key), $_COOKIE[$key]);
            }
        }

        return $request;
    }
}
