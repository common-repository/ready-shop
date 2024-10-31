<?php
/**
 * @author Daniel P. Baddeley JR <dan@bluewatermedia.tv>
 * @date: 9/14/17 6:17 PM
 * @info Will be used to manage application settings from the UI [Backend of Wordpress Installation]
 * @todo need to implement settings form to be stored in DB & Able To Be Pulled On Login
 * - For now settings and commission are tracked automatically after wordpress plugin installation & is tied to the
 * application token generated for the end user, all sales and commission can be viewed through this [appToken]
 */

namespace ReadyShop\lib;

require_once 'ReadyShop_APIService.php';
require_once 'ReadyShop_PackageManager.php';
require_once 'ReadyShop_UserService.php';

use ReadyShop\lib\ReadyShop_APIService as ReadyShop_APIService;
use ReadyShop\lib\ReadyShop_PackageManager as ReadyShop_PackageManager;
use ReadyShop\lib\ReadyShop_UserService as ReadyShop_UserService;

/**
 * Class ReadyShop_SettingsService
 * @package lib
 */
class ReadyShop_SettingsService
{

    /**
     * Will fetch array of settings information to auto-populate the client-side settings form
     */
    public static function getSettings() {
        return ReadyShop_APIService::get('/wp/settings/' . ReadyShop_PackageManager::getApplicationToken() . '?userId=' . ReadyShop_UserService::getUser()['userId'] );
    }

    /**
     * Will transmit array of settings information to be stored in API
     * @param array $data
     * @return array
     */
    public static function setSettings(array $data) {
        return ReadyShop_APIService::post('/wp/settings/' . ReadyShop_PackageManager::getApplicationToken(), $data);
    }

    /**
     * Returns Registration Status Based On Settings Information
     * --- Should Honestly Always Return "TRUE" Just A(n) Added Validation Check
     * @param $settings
     * @return bool
     */
    public static function checkRegistrationStatus($settings) {
        if($settings['firstName'] != ''
            && $settings['lastName'] != ''
            && $settings['email'] != ''
            && $settings['address1'] != ''
            && $settings['password1'] != ''
            && $settings['city'] != ''
            && $settings['state'] != ''
            && $settings['zipcode'] != ''
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * This will check to see if the paypal email has been provided for commission payouts
     * @param $settings
     * @return bool
     */
    public static function checkPaypalEmailForCommissionPayment($settings) {
        return ($settings['paypalEmail'] != '') ? true : false;
    }


    /**
     * This will check to see if the W9 Tax Form has been filled out by the end customer to receive $600+ in payouts
     * @param $settings
     * @return bool
     */
    public static function checkW9TaxFormRegistration($settings) {
        if($settings['fullName'] != ''
            && $settings['businessName'] != ''
            && $settings['inputTaxClassification'] != ''
            && $settings['businessAddress1'] != ''
            && $settings['businessCity'] != ''
            && $settings['businessState'] != ''
            && $settings['businessZipcode'] != ''
            && $settings['socialSecurity'] != ''
            && $settings['employerIdentificationNumber'] != ''
        ) {
            return true;
        } else {
            return false;
        }
    }
}