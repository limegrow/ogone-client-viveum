<?php
/**
 * Created by PhpStorm.
 * User: alexw
 * Date: 24/01/19
 * Time: 14:52.
 */

namespace IngenicoClient;

/**
 * Class Onboarding.
 */
class Onboarding
{
    protected $emails;

    /**
     * Onboarding constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $iniFile = __DIR__.'/../onboarding/emails.ini';
        if (!file_exists($iniFile)) {
            throw new Exception('Cannot find onboarding email file: onboarding/emails.ini');
        }

        if (!$data = parse_ini_file($iniFile, true)) {
            throw new Exception('Cannot parse onboarding email file: onboarding/emails.ini');
        }

        if (!isset($data['emails'])) {
            throw new Exception('There is no "emails" section in onboarding email file');
        }

        $this->emails = $data['emails'];
    }

    /**
     * get array of emails by country code.
     *
     * @param string $countryCode - 2-chars country code
     *
     * @return array
     */
    public function getOnboardingEmailsByCountry($countryCode)
    {
        return isset($this->emails[$countryCode]) ? $this->emails[$countryCode] : array();
    }
}
