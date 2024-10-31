<?php
/**
 * @author Daniel P. Baddeley JR <dan@bluewatermedia.tv>
 * @date 5/15/18
 * @time 11:25 AM
 * @info This class file will be used to make sure the plugin has been installed to the perfect environment.
 */

namespace ReadyShop\lib;


/**
 * Class ReadyShop_EnvironmentChecks
 * @package ReadyShop\lib
 */
class ReadyShop_EnvironmentChecks
{
    /** @var bool Needed For Communicating With 3rd Party API's */
    const REQUIRED_ALLOW_URL_FOPEN_SETTING = true;

    const RECOMMENDED_OPCACHE_ENABLED = true;


    /**
     * This function will check the configuration options and environment settings that should be enabled during activation
     * @return array
     */
    public static function checkSettings() {
        $settings = [];

        if( ini_get('allow_url_fopen') == static::REQUIRED_ALLOW_URL_FOPEN_SETTING || ini_get('allow_url_fopen') == "On") {
            $settings['allow_url_fopen'] = 'passed';
        } else {
            $settings['allow_url_fopen'] = 'failed';
        }


        if(ini_get('opcache.enable') == static::RECOMMENDED_OPCACHE_ENABLED || ini_get('opcache.enable') == "On") {
            $settings['opcache.enable'] = 'passed';
        } else {
            $settings['opcache.enable'] = 'failed';
        }

        return $settings;
    }


    /**
     * This function will display error | warning | info alerts if there are any configuration or environment conflicts
     */
    public static function displayConfigurationAlerts() {
        //fetch system configuration settings results
        $settings = static::checkSettings();

        //iterate through settings results & display [error | warning | info] alerts if conflicts exist
        foreach($settings as $key => $val) {
            switch($key) {
                case 'allow_url_fopen':
                    if($val === 'failed') {
                        echo '<p class="alert alert-danger"><strong>Warning!</strong>&nbsp;PHP: [allow_url_fopen] Configuration Setting is set to [false] and is disabled!</p>';
                        echo '<p class="alert alert-info"><strong>How To Fix!</strong>&nbsp;PHP: [allow_url_fopen] Must Be Set To [true] Contact Your Hosting Provider! <a target="_blank" href="http://php.net/manual/en/filesystem.configuration.php">More Information</a></p>';
                    }
                    break;
            }
        }
    }

    /**
     * This function will handle settings for the file system to remove responsibility from the end customer
     * @todo Need To Handle Op-Cache Being Enabled & Clear the cache using [opcache_reset()] to force clear the cache on updating
     */
    public static function handleInternalConfigurationSettings() {

        if(self::checkSettings()['opcache.enable'] === 'passed') {
            $versionLock = file_get_contents(__DIR__.'/../var/version-lock');

            //create local file to check version of plugin installation for future updates|upgrades
            //clear opcache when version change is detected
            if($versionLock !== ReadyShop::PLUGIN_VERSION) {
                opcache_reset();
                file_put_contents(__DIR__ . '/../var/version-lock',ReadyShop::PLUGIN_VERSION);
            }
        }

    }


    public static function debugEnvironment() {
        return array();
    }
}