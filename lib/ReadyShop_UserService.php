<?php
/**
 * @author Daniel P. Baddeley JR <dan@bluewatermedia.tv>
 * @date: 9/14/17 6:10 PM
 * @info This service is used to authenticate and login the user in case of domain changes
 * @todo need to implement login service
 * -- For now the user application is automatically registered to their domain when they download or install the plugin
 */

namespace ReadyShop\lib;

require_once 'ReadyShop_APIService.php';
require_once 'ReadyShop_SettingsService.php';

use ReadyShop\lib\ReadyShop_APIService as ReadyShop_APIService;
use ReadyShop\lib\ReadyShop_SettingsService as ReadyShop_SettingsService;

/**
 * Class UserService
 * @package lib
 */
class ReadyShop_UserService
{

    /**
     * Will Authenticate User Credentials & Authorize User For System Usage
     * @param $args
     * @return array|mixed|false
     */
    public static function loginUser($args) {
        $response = ReadyShop_APIService::post('/wp/auth',$args);
        if(self::setStatelessToken($response['token'])) {
            return $response;
        } else {
            return false;
        }
    }

    /**
     * Will remove the stateless token from the [user-token] file in the [^/var] directory
     * @return bool
     */
    public static function logoutUser() {
        $token = 'null';
        file_put_contents(__DIR__.'/../var/user-token', $token ,FILE_TEXT);

        //check that the file was successfully overwritten
        if(file_get_contents(__DIR__.'/../var/user-token') == 'null') {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Grabs The Token Information Stored Locally After Successful Login Attempt
     * @return array
     */
    public static function getUser() {https://codex.wordpress.org/Global_Variables
        $token = file_get_contents(__DIR__.'/../var/user-token');
        $token = base64_decode($token);
        $user = explode(':',$token);
        $user = array(
            'userId' => $user[0],
            'appToken' => $user[1],
            'settingsId' => $user[2]
        );
        return $user;
    }

    /**
     * This will set the stateless token for the user
     * @param $token
     * @return bool
     */
    public static function setStatelessToken($token) {
        //put the token in the [user-token] file
        file_put_contents(__DIR__.'/../var/user-token',$token,FILE_TEXT);

        //check to see that the token was written properly
        if(file_get_contents(__DIR__.'/../var/user-token') === $token) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Detect User Authentication State
     * @return bool
     */
    public static function isLoggedIn() {
        $settings = ReadyShop_SettingsService::getSettings();
        $loggedIn = (isset($settings['firstName']) && $settings['firstName'] != '') ? true : false;
        return $loggedIn;
    }

}