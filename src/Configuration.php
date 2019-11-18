<?php

namespace IngenicoClient;

use Ogone\Passphrase;
use Ogone\HashAlgorithm;
use InvalidArgumentException;
use Ogone\ShaComposer\AllParametersShaComposer;
use Ogone\ParameterFilter\ShaOutParameterFilter;
use Ogone\ParameterFilter\ShaInParameterFilter;

/**
 * Class Configuration
 * @method mixed getConnectionMode()
 * @method $this setConnectionMode($mode)
 * @method mixed getConnectionTestPspid()
 * @method $this setConnectionTestPspid($value)
 * @method mixed getConnectionLivePspid()
 * @method $this setConnectionLivePspid($value)
 * @method mixed getConnectionTestDlUser()
 * @method $this setConnectionTestDlUser($value)
 * @method mixed getConnectionLiveDlUser()
 * @method $this setConnectionLiveDlUser($value)
 * @method mixed getConnectionTestDlPassword()
 * @method $this setConnectionTestDlPassword($value)
 * @method mixed getConnectionLiveDlPassword()
 * @method $this setConnectionLiveDlPassword($value)
 * @method mixed getConnectionTestSignature()
 * @method $this setConnectionTestSignature($value)
 * @method mixed getConnectionLiveSignature()
 * @method $this setConnectionLiveSignature($value)
 * @method mixed getConnectionTestAlgorithm()
 * @method $this setConnectionTestAlgorithm($value)
 * @method mixed getConnectionLiveAlgorithm()
 * @method $this setConnectionLiveAlgorithm($value)
 * @method mixed getSettingsOrderfreezeDays()
 * @method $this setSettingsOrderfreezeDays($value)
 * @method mixed getSettingsReminderemailDays()
 * @method $this setSettingsReminderemailDays($value)
 * @method mixed getFraudNotificationsEmail()
 * @method $this setFraudNotificationsEmail($value)
 * @method mixed getDirectSaleEmail()
 * @method $this setDirectSaleEmail($value)
 * @method mixed getPaymentpageType()
 * @method $this setPaymentpageType($value)
 * @method mixed getPaymentpageTemplate()
 * @method $this setPaymentpageTemplate($value)
 * @method mixed getPaymentpageTemplateName()
 * @method $this setPaymentpageTemplateName($value)
 * @method mixed getPaymentpageTemplateExternalurl()
 * @method $this setPaymentpageTemplateExternalurl($value)
 * @method mixed getPaymentpageTemplateLocalfilename()
 * @method $this setPaymentpageTemplateLocalfilename($value)
 * @method mixed getInstalmentsType()
 * @method $this setInstalmentsType($value)
 * @method mixed getInstalmentsFixedInstalments()
 * @method $this setInstalmentsFixedInstalments($value)
 * @method mixed getInstalmentsFixedPeriod()
 * @method $this setInstalmentsFixedPeriod($value)
 * @method mixed getInstalmentsFixedFirstpayment()
 * @method $this setInstalmentsFixedFirstpayment($value)
 * @method mixed getInstalmentsFixedMinpayment()
 * @method $this setInstalmentsFixedMinpayment($value)
 * @method mixed getInstalmentsFlexInstalmentsMin()
 * @method $this setInstalmentsFlexInstalmentsMin($value)
 * @method mixed getInstalmentsFlexInstalmentsMax()
 * @method $this setInstalmentsFlexInstalmentsMax($value)
 * @method mixed getInstalmentsFlexPeriodMin()
 * @method $this setInstalmentsFlexPeriodMix($value)
 * @method mixed getInstalmentsFlexPeriodMax()
 * @method $this setInstalmentsFlexPeriodMax($value)
 * @method mixed getInstalmentsFlexFirstpaymentMax()
 * @method $this setInstalmentsFlexFirstpaymentMax($value)
 * @method mixed getSettingsAdvanced()
 * @method $this setSettingsAdvanced($value)
 * @method mixed getSettingsTokenisation()
 * @method $this setSettingsTokenisation($value)
 * @method $this setSettingsOneclick($value)
 * @method mixed getSettingsSkip3dscvc()
 * @method $this setSettingsSkip3dscvc($value)
 * @method mixed getSettingsSkipsecuritycheck()
 * @method $this setSettingsSkipsecuritycheck($value)
 * @method mixed getSecure()
 * @method $this setSecure($value)
 * @method mixed getSettingsDirectsales()
 * @method $this setSettingsDirectsales($value)
 * @method mixed getSettingsOrderfreeze()
 * @method $this setSettingsOrderfreeze($value)
 * @method mixed getSettingsReminderemail()
 * @method $this setSettingsReminderemail($value)
 * @method mixed getFraudNotifications()
 * @method $this setFraudNotifications($value)
 * @method mixed getDirectSaleEmailOption()
 * @method $this setDirectSaleEmailOption($value)
 * @method mixed getInstalmentsEnabled()
 * @method $this setInstalmentsEnabled($value)
 * @method mixed getSelectedPaymentMethods()
 * @method $this setSelectedPaymentMethods($value)
 *
 * @package IngenicoClient
 */
class Configuration extends Data
{
    /**
     * Mode
     */
    const MODE_PRODUCTION = true;
    const MODE_TEST = false;

    /**
     * Hash Algorithm
     */
    const HASH_SHA1 = 'sha1';
    const HASH_SHA256 = 'sha256';
    const HASH_SHA512 = 'sha512';

    /**
     * Payment Page Types
     */
    const PAYMENT_TYPE_REDIRECT = 'REDIRECT';
    const PAYMENT_TYPE_INLINE = 'INLINE';

    /**
     * Payment Page Templates
     */
    const PAYMENT_PAGE_TEMPLATE_INGENICO = 'INGENICO';
    const PAYMENT_PAGE_TEMPLATE_STORE = 'STORE';
    const PAYMENT_PAGE_TEMPLATE_EXTERNAL = 'EXTERNAL';

    /**
     * Installments Types
     */
    const INSTALMENTS_TYPE_FIXED = 'FIXED';
    const INSTALMENTS_TYPE_FLEXIBLE = 'FLEXIBLE';

    /**
     * Default Settings
     * @var array
     */
    private static $default_settings = [
        'connection_mode' => self::MODE_TEST,
        'connection_test_algorithm' => self::HASH_SHA512,
        'connection_test_pspid' => null,
        'connection_test_signature' => null,
        'connection_test_dl_user' => null,
        'connection_test_dl_password' => null,
        'connection_test_dl_timeout' => 30,
        'connection_test_webhook' => null,
        'connection_live_algorithm' => self::HASH_SHA512,
        'connection_live_pspid' => null,
        'connection_live_signature' => null,
        'connection_live_dl_user' => null,
        'connection_live_dl_password' => null,
        'connection_live_dl_timeout' => 30,
        'connection_live_webhook' => null,
        'settings_orderfreeze_days' => 3,
        'settings_reminderemail_days' => 2,
        'fraud_notifications_email' => null,
        'direct_sale_email' => null,
        'paymentpage_type' => self::PAYMENT_TYPE_REDIRECT,
        'paymentpage_template' => self::PAYMENT_PAGE_TEMPLATE_INGENICO,
        'paymentpage_template_name' => null,
        'paymentpage_template_externalurl' => null,
        'paymentpage_template_localfilename' => null,
        'instalments_type' => self::INSTALMENTS_TYPE_FIXED,
        'instalments_fixed_instalments' => 3,
        'instalments_fixed_period' => 30,
        'instalments_fixed_firstpayment' => 20,
        'instalments_fixed_minpayment' => 50,
        'instalments_flex_instalments_min' => 2,
        'instalments_flex_instalments_max' => 5,
        'instalments_flex_period_min' => 30,
        'instalments_flex_period_max' => 90,
        'instalments_flex_firstpayment_min' => 10,
        'instalments_flex_firstpayment_max' => 50,
        'settings_advanced' => false,
        'settings_tokenisation' => true,
        'settings_oneclick' => true,
        'settings_skip3dscvc' => true,
        'settings_skipsecuritycheck' => true,
        'secure' => true,
        'settings_directsales' => true,
        'settings_orderfreeze' => true,
        'settings_reminderemail' => true,
        'fraud_notifications' => true,
        'direct_sale_email_option' => false,
        'instalments_enabled' => false,
        'selected_payment_methods' => [],
    ];

    /** @var ConnectorInterface */
    private $extension;

    /** @var IngenicoCoreLibraryInterface */
    private $coreLibrary;

    /**
     * Configuration constructor.
     * @param ConnectorInterface $extension
     * @param IngenicoCoreLibraryInterface $coreLibrary
     */
    public function __construct(
        ConnectorInterface $extension,
        IngenicoCoreLibraryInterface $coreLibrary
    ) {
        $this->extension = $extension;
        $this->coreLibrary = $coreLibrary;

        // Set default settings
        foreach (self::$default_settings as $key => $value) {
            $this->setData($key, $value);
        }
    }

    /**
     * Set Data
     * @param $key
     * @param mixed|null $value
     * @return $this
     */
    public function setData($key, $value = null) {
        if (is_string($key)) {
            $key = str_replace('.', '_', strtolower($key));
        }

        if ($value === 'on') {
            return parent::setData($key, true);
        }

        if ($value === 'off') {
            return parent::setData($key, false);
        }

        return parent::setData($key, $value);
    }

    /**
     * Load Configuration.
     *
     * @param array $data
     */
    public function load(array $data)
    {
        $this->setData($data);

        // Post load
        // Inline method always have "Ingenico" template
        if ($this->getPaymentpageType() === self::PAYMENT_TYPE_INLINE) {
            $this->setPaymentpageTemplate(self::PAYMENT_PAGE_TEMPLATE_INGENICO);
        }
    }

    /**
     * Get Shopping Cart Extension Id.
     *
     * @return string
     */
    public function getShoppingCartExtensionId()
    {
        return $this->extension->requestShoppingCartExtensionId();
    }

    /**
     * Get Default Settings.
     *
     * @return array
     */
    public static function getDefault()
    {
        return self::$default_settings;
    }

    /**
     * Get Mode
     * @return mixed
     */
    public function getMode()
    {
        return $this->getConnectionMode();
    }

    /**
     * Check is Test Mode activated.
     *
     * @return bool
     */
    public function isTestMode()
    {
        return $this->getConnectionMode() === self::MODE_TEST;
    }

    /**
     * Set Mode
     * @param $value
     * @return $this
     */
    public function setMode($value)
    {
        return $this->setConnectionMode($value);
    }

    /**
     * Get PSPId
     * @return string
     */
    public function getPspid()
    {
        if ($this->getConnectionMode() === self::MODE_PRODUCTION) {
            return $this->getConnectionLivePspid();
        }

        return $this->getConnectionTestPspid();
    }

    /**
     * Set PSPId
     *
     * @param $pspid
     * @return $this
     */
    public function setPspid($pspid)
    {
        if (strlen($pspid) > 30) {
            throw new InvalidArgumentException('PSPId is too long');
        }

        if ($this->getConnectionMode() === self::MODE_PRODUCTION) {
            return $this->setConnectionLivePspid($pspid);
        }

        return $this->setConnectionTestPspid($pspid);
    }

    /**
     * Get User Id
     * @return mixed
     */
    public function getUserId()
    {
        if ($this->getConnectionMode() === self::MODE_PRODUCTION) {
            return $this->getConnectionLiveDlUser();
        }

        return $this->getConnectionTestDlUser();
    }

    /**
     * Set API user
     *
     * @param string $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        if (strlen($userId) < 2) {
            throw new InvalidArgumentException('User ID is too short');
        }

        if ($this->getConnectionMode() === self::MODE_PRODUCTION) {
            return $this->setConnectionLiveDlUser($userId);
        }

        return $this->setConnectionTestDlUser($userId);
    }

    /**
     * Set API Password
     * @return string
     */
    public function getPassword()
    {
        if ($this->getConnectionMode() === self::MODE_PRODUCTION) {
            return $this->getConnectionLiveDlPassword();
        }

        return $this->getConnectionTestDlPassword();
    }

    /**
     * Set API user password
     *
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Password is too short');
        }

        if ($this->getConnectionMode() === self::MODE_PRODUCTION) {
            return $this->setConnectionLiveDlPassword($password);
        }

        return $this->setConnectionTestDlPassword($password);
    }

    /**
     * Get passphrase
     * @return string
     */
    public function getPassphrase()
    {
        if ($this->getConnectionMode() === self::MODE_PRODUCTION) {
            return $this->getConnectionLiveSignature();
        }

        return $this->getConnectionTestSignature();
    }

    /**
     * Set passphrase
     *
     * @param string $passphrase
     * @return $this
     */
    public function setPassphrase($passphrase)
    {
        if (!is_string($passphrase)) {
            throw new \InvalidArgumentException('String expected');
        }

        if ($this->getConnectionMode() === self::MODE_PRODUCTION) {
            return $this->setConnectionLiveSignature($passphrase);
        }

        return $this->setConnectionTestSignature($passphrase);
    }

    /**
     * Get SHA algorithm
     * @return string
     */
    public function getAlgorithm()
    {
        if ($this->getConnectionMode() === self::MODE_PRODUCTION) {
            return $this->getConnectionLiveAlgorithm();
        }

        return $this->getConnectionTestAlgorithm();
    }

    /**
     * Set SHA algorithm
     *
     * @param string $algorithm
     * @return $this
     */
    public function setAlgorithm($algorithm)
    {
        if (!in_array($algorithm, [self::HASH_SHA1, self::HASH_SHA256, self::HASH_SHA512])) {
            throw new \InvalidArgumentException(
                $algorithm . ' is not supported, only sha1, sha256 and sha512 are allowed.'
            );
        }

        if ($this->getConnectionMode() === self::MODE_PRODUCTION) {
            return $this->setConnectionLiveAlgorithm($algorithm);
        }

        return $this->setConnectionTestAlgorithm($algorithm);
    }

    /**
     * Get if Stored Card enabled
     * @return bool
     */
    public function getSettingsOneclick()
    {
        return (bool) $this->getData('settings_oneclick');
    }

    /**
     * Validate field's value.
     * Returns true or error message.
     *
     * @param string $fieldKey
     * @param string $fieldValue
     * @return bool|string
     */
    public function validate($fieldKey, $fieldValue)
    {
        if (strpos($fieldKey, 'instalments_') !== false) {
            if ($fieldValue < 0) {
                // Instalments negative values are not valid.
                return $this->coreLibrary->__('validator.instalments.negative_values');
            }

            if ($fieldKey === 'instalments_fixed_instalments' && $fieldValue > 24) {
                // Order can be split up to 24 instalment.
                return $this->coreLibrary->__('validator.instalments.too_much');
            }

            if ($fieldKey === 'instalments_fixed_period' && $fieldValue > 90) {
                // Maximum period between each instalment is 90 days.
                return $this->coreLibrary->__('validator.instalments.maximum_period');
            }
            if ($fieldKey === 'instalments_fixed_firstpayment' && ($fieldValue < 1 || $fieldValue > 99)) {
                if ( $fieldValue < 1 || $fieldValue > 99 ) {
                    // First payment must be between 1% - 99%.
                    return $this->coreLibrary->__('validator.instalments.first_payment');
                }
            }
        }

        if (in_array($fieldKey, ['connection_test_dl_timeout', 'connection_live_dl_timeout']) && $fieldValue < 0) {
            // Request timeout is not valid.
            return $this->coreLibrary->__('validator.request_timeout');
        }

        if (in_array($fieldKey, ['connection_test_signature', 'connection_live_signature'])) {
            if (!empty($fieldValue) && strlen($fieldValue) < 40 ) {
                return $this->coreLibrary->__('validator.short_signature');
            }
        }

        /** Validate template url */
        if ($fieldKey === 'paymentpage_template_externalurl') {
            $url = strpos($fieldValue, 'http' ) !== 0 ? "http://{$fieldValue}" : $fieldValue;
            if (!empty($fieldValue) && !filter_var($url, FILTER_VALIDATE_URL)) {
                // Template file URL is not valid.
                return $this->coreLibrary->__('validator.template_url_invalid');
            }
        }

        if ($fieldKey === 'settings_reminderemail_days') {
            if ($fieldValue < 0) {
                // Reminder negative values are not valid.
                return $this->coreLibrary->__('validator.settings.reminderemail.negative_values');
            }
        }

        return true;
    }

    /**
     * Save Configuration.
     * Use for saving configuration on connector's side.
     *
     * @return void
     * @throws Exception
     */
    public function save()
    {
        $errors = [];
        foreach ($this->getData() as $fieldKey => $fieldValue) {
            // Validate value
            $error = $this->validate($fieldKey, $fieldValue);
            if (is_string($error)) {
                $errors[] = $error;
            } else {
                // Save value
                try {
                    $this->extension->saveSetting($this->getConnectionMode(), $fieldKey, $fieldValue);
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }

            }
        }

        if (count($errors) > 0) {
            throw new Exception(sprintf('Validation errors: %s', implode("\n", $errors)));
        }
    }

    /**
     * Copy "Test" Configuration to "Live".
     *
     * @return Configuration
     */
    public function copyToLive()
    {
        $testConf = new Configuration($this->extension, $this->coreLibrary);
        $testConf->setData($this->extension->requestSettings(self::MODE_TEST));
        $testConf->setConnectionMode(self::MODE_PRODUCTION);

        // Copy values
        foreach ($testConf->getData() as $fieldKey => $fieldValue) {
            if (in_array($fieldKey, [
                    'connection_test_pspid', 'connection_test_dl_user',
                    'connection_test_dl_password', 'connection_test_signature', 'connection_test_algorithm']
            )) {
                $testConf->setData(str_replace('test', 'live', $fieldKey), $fieldValue);
            }
        }

        try {
            $testConf->save();
        } catch (\Exception $e) {
            // Ignore validation errors
        }

        return $testConf;
    }

    /**
     * Export Configuration.
     *
     * @return array
     */
    public function export()
    {
        $conf = [
            'extension_id' => $this->extension->requestShoppingCartExtensionId(),
            'date' => date('Y-m-d H:i:s'),
            'test' => $this->extension->requestSettings(self::MODE_TEST),
            'production' => $this->extension->requestSettings(self::MODE_PRODUCTION)
        ];

        // Remove sensitive data
        foreach (['test', 'production'] as $mode) {
            foreach (
                [
                    'connection_test_pspid',
                    'connection_test_signature',
                    'connection_test_dl_user',
                    'connection_test_dl_password',
                    'connection_live_pspid',
                    'connection_live_signature',
                    'connection_live_dl_user',
                    'connection_live_dl_password',
                ] as $key)
            {
                unset($conf[$mode][$key]);
            }
        }

        return $conf;
    }

    /**
     * Import Configuration.
     *
     * @param array $data
     * @throws Exception
     */
    public function import(array $data)
    {
        if (isset($data['test'])) {
            $conf = new Configuration($this->extension, $this->coreLibrary);
            $conf->setData($data['test']);
            $conf->setConnectionMode(self::MODE_TEST);
            $conf->save();
        }

        if (isset($data['production'])) {
            $conf = new Configuration($this->extension, $this->coreLibrary);
            $conf->setData($data['production']);
            $conf->setConnectionMode(self::MODE_PRODUCTION);
            $conf->save();
        }

        // Set active mode
        $mode = $data['production']['connection_mode'];
        $conf = new Configuration($this->extension, $this->coreLibrary);
        $conf->setData($this->extension->requestSettings($mode));
        $conf->setConnectionMode($mode);
        $conf->save();
    }

    /**
     * Get SHA composer
     *
     * @param $direction
     * @return AllParametersShaComposer
     */
    public function getShaComposer($direction = null)
    {
        $shaComposer = new AllParametersShaComposer(
            new Passphrase($this->getPassphrase()),
            new HashAlgorithm($this->getAlgorithm())
        );

        switch ($direction) {
            case 'in':
                $shaComposer->addParameterFilter(new ShaInParameterFilter);
                break;
            case 'out':
                $shaComposer->addParameterFilter(new ShaOutParameterFilter);
                break;
        }

        return $shaComposer;
    }
}