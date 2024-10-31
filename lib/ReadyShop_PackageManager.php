<?php
/**
 * This File Will Contain The Operations Used To Manage API Request For Embedded & IFramed Content
 * @todo Allow This File To Record Installations On Different Domains
 * @todo Allow This File To Record Uninstalls On Different Domains
 *
 * The purpose of these features are to allow our system to see what websites are currently using our plugin so we can
 * show an [active || inactive] status on the different domains from our backend portal
 *
 * This system should also be able to send out a request based on the domain to return a system id for the site to append to api request URLs
 * to track where the traffic is coming from to be able to update the [^/activity] page in the backend admin portal
 *
 * @author Daniel P. Baddeley JR <dan@bluewatermedia.tv>
 * @version 1.0
 */

namespace ReadyShop\lib;

require_once 'ReadyShop.php';
require_once 'ReadyShop_APIService.php';
require_once 'ReadyShop_EnvironmentChecks.php';

use ReadyShop\lib\ReadyShop as ReadyShop;
use ReadyShop\lib\ReadyShop_APIService as ReadyShop_APIService;
use ReadyShop\lib\ReadyShop_EnvironmentChecks as ReadyShop_EnvironmentChecks;

/**
 * Class ReadyShop_PackageManager
 * @package lib
 */
class ReadyShop_PackageManager
{

    /**
     * @var string This will map to the application(s) domain and will be used for recording system metrics
     */
    private static $applicationToken;


    /**
     * Should Register The System Plugin & Return An Activation Token For Further API Request Usage
     */
    public static function activation() {
        $shopPageCreated = false;
        $shopPageNameExists = false;
        $readyShopPageNameExists = false;


        $body = array(
            'host' => $_SERVER['HTTP_HOST'],
            'domain' => $_SERVER['SERVER_NAME'],
            'type' => 'wordpress',
        );


        $response = ReadyShop_APIService::post('/v1.0/installation/activation',$body);


        if(isset($response['token'])) {
            self::setApplicationToken($response['token']);
        } else {
            self::setApplicationToken('null');
        }

        global $wpdb;
        $user_id = get_current_user_id();

        //include [post_exists] function if not set to prevent fatal errors
        if ( ! function_exists( 'post_exists' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/post.php' );
        }

        //check if [Shop] post title exists
        $postExistsCheckId = post_exists('Shop');
        if($postExistsCheckId > 0) {
            $post = get_post($postExistsCheckId);
            $shopPageNameExists = true;

            if(strpos($post->post_content,'[ready_shop]') !== false) {
                $shopPageCreated = true;
            } else {
                $shopPageCreated = false;
            }
        }

        //check if [Ready Shop] post title exists
        if($shopPageNameExists && $shopPageCreated === false) {
            $postExistsCheckId = post_exists('Ready Shop');
            if($postExistsCheckId > 0) {
                $post = get_post($postExistsCheckId);
                $readyShopPageNameExists = true;

                if(strpos($post->post_content,'[ready_shop]') !== false) {
                    $shopPageCreated = true;
                } else {
                    $shopPageCreated = false;
                }
            }
        }


        //set the shop name if it doesn't already exist
        $shopPageName = (!$shopPageNameExists) ? 'Shop' : 'Ready Shop';

        //@todo detect if [post_name] already exists. IF *SO* append *_1|2|3 etc. increments to page name.
        //@link https://wordpress.org/plugins/ready-shop/#developers
        $post = array(
            'menu_order' => 0, //If new post is a page, sets the order should it appear in the tabs.
            'page_template' => '', //Sets the template for the page.
            'comment_status' => 'closed', // 'closed' means no comments. 'open' means comments allowed
            //'ping_status' => [ ? ], //Ping status?
            //'pinged' => [ ? ], //?
            'post_author' => $user_id, //The user ID number of the author.
            'post_category' => '', //Add some categories.
            'post_content' => '[ready_shop]', //The full text of the post.
            'post_date' => date('Y-m-d H:i:s', strtotime('2018-01-01')), //The time post was made.
            //'post_date_gmt' => , //The time post was made, in GMT.
            //'post_excerpt' => [ <an excerpt> ], //For all your post excerpt needs.
            'post_name' => 'shop', // The name (slug) for your post
            //'post_parent' => '', //Sets the parent of the new post.
            //'post_password' => [ ? ], //password for post?
            'post_status' => 'publish', //[ 'draft' | 'publish' | 'pending' ] //Set the status of the new post.
            'post_title' => $shopPageName, //The title of your post.
            'post_type' => 'page', //[ 'post' | 'page' ], //Sometimes you want to post a page.
            //'tags_input' => '',//[ '<tag>, <tag>, <...>' ], //For tags.
            //'to_ping' => [ ? ], //?
        );

        // Insert the post into the database
        // @todo re-enable this when ironed out further
        if(!$shopPageCreated) {
           $postId = wp_insert_post( $post );

            // Get existing menu locations assignments
            $menu_locations = get_nav_menu_locations();

            //add to all menus
            foreach($menu_locations as $menuKey => $menuLocation) {
                // Get Top-Level Menu
                $topMenu = wp_get_nav_menu_object($menuLocation);




                if(isset($postId) && self::page_in_menu($topMenu->slug,$postId)){
                    //do nothing
                }else{
                    //add to menu
                    $post = get_post($postId);
                    $pageURL = get_page_link($post);
                    //write navigation link to file to be used on page load.



//                //Add New Page To Top-Level Menu
                    wp_update_nav_menu_item($topMenu->term_id,0,array(
                        'menu-item-title' => $post->post_title,
                        'menu-item-object-id' => $post->ID,
                        'menu-item-object' => 'page',
                        'menu-item-status' => 'publish',
                        'menu-item-type' => 'post_type'
                    ));


                }
            }

        } else {

            /**
             * Should Navigation Menu Be Added AFTER Initial Activation on Re-Activation?
             */


//            // Get existing menu locations assignments
//            $menu_locations = get_nav_menu_locations();
//            // Get Top-Level Menu
//            $topMenu = wp_get_nav_menu_object($menu_locations['top']);
//
//            if(isset($post->ID) && self::page_in_menu($topMenu->slug,$post->ID)){
//                //do nothing
//            }else{
//                //add to menu
//                $post = get_post($post->ID);
//                $pageURL = get_page_link($post);
//                //write navigation link to file to be used on page load.
//
//                // Get existing menu locations assignments
//                $menu_locations = get_nav_menu_locations();
//                // Get Top-Level Menu
//                $topMenu = wp_get_nav_menu_object($menu_locations['top']);
//
//                //Add New Page To Top-Level Menu
//                wp_update_nav_menu_item($topMenu->term_id,0,array(
//                    'menu-item-title' => $shopPageName,
//                    'menu-item-object-id' => $post->ID,
//                    'menu-item-object' => 'page',
//                    'menu-item-status' => 'publish',
//                    'menu-item-type' => 'post_type'
//                ));
//            }
        }





        //Run Installation & Environment Checks
        ReadyShop_EnvironmentChecks::handleInternalConfigurationSettings();

    }

    /**
     * Should Deactivate The System Plugin & Set The Token To [Null]
     */
    public static function deactivation() {

        $body = array(
            'token' => self::getApplicationToken(),
            'host' => $_SERVER['HTTP_HOST'],
            'domain' => $_SERVER['SERVER_NAME'],
            'type' => 'wordpress',
        );

        $response = ReadyShop_APIService::post('/v1.0/installation/deactivation', $body);

        if(isset($response['token'])) {
            self::setApplicationToken('deactivated');
        }
    }

    /**
     * This will perform an API request to validate the applications registration and return an application code for further API requests
     */
    private static function setApplicationToken($token = null) {
        if(isset($token)) {
            self::$applicationToken = $token;
            file_put_contents(__DIR__.'/../var/application-token',$token,FILE_TEXT);
            return;
        } else {

            $body = array(
                'host' => $_SERVER['HTTP_HOST'],
                'domain' => $_SERVER['SERVER_NAME'],
                'type' => 'wordpress',
            );

            $response = ReadyShop_APIService::post('/v1.0/installation/token', $body);

            if(isset($response['token'])) {
                self::setApplicationToken($response['token']);
            }
        }
    }

    /**
     * @return string
     */
    public static function getApplicationToken() {
        if(isset(self::$applicationToken) && self::$applicationToken != '') {
            return self::$applicationToken;
        } else {
            $token = file_get_contents(__DIR__.'/../var/application-token');
            if($token != 'null' && $token != 'deactivated') {
                self::setApplicationToken($token);
                return self::$applicationToken;
            } else {
                self::setApplicationToken();
                return self::$applicationToken;
            }
        }
    }

    /**
     * Check To See If All The Installation Steps Have Been Completed *IF NOT* Keep them on the installation Screen After "LOGIN" Registration
     * @return bool
     */
    public static function checkRegistrationCompletion() {
        $status = file_get_contents(__DIR__ . '/../var/registration-process');
        $statuses = explode(':', $status);

        $isCompleted = true;
        foreach($statuses as $key => $value) {
            if($value !== 'completed') {
                $isCompleted = false;
            }
        }


        return $isCompleted;
    }


    /**
     * Will update the individual registration steps
     * @param $step
     * @param $value
     * @return bool
     * @todo need to remove the muted exceptions.... bad practice, but will keep from throwing 500 errors on bad permissions
     * @todo need to store this remote to allow persistence [Un-installation | Re-Installation | Login]
     */
    public static function updateRegistrationStep($step, $value) {
        //get the step statuses & convert to array
        $status = @file_get_contents(__DIR__ . '/../var/registration-process');
        $statuses = explode(':',$status);

        //translate the step status
        if($value === true) {
            $value = 'completed';
        } else {
            $value = 'null';
        }

        //change the step value
        $statuses[$step] = $value;

        //revert to string
        $status = implode(':', $statuses);

        //write change to disk
        @file_put_contents(__DIR__ . '/../var/registration-process',$status);

        //check that the information was written to disk correctly
        if($status === file_get_contents(__DIR__ . '/../var/registration-process')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns A Boolean Response After Checking The Desired Value For The Registration Step
     *
     * @param $step
     * @param $value
     * @return bool
     */
    public static function checkRegistrationStep($step, $value) {
        $status = @file_get_contents(__DIR__ . '/../var/registration-process');
        $statuses = explode(':', $status);

        return ($statuses[$step] === $value) ? true : false;
    }


    /**
     * Internal Function To Find If A Page Exists In A Selected WP Menu By [slug]
     * @param null $menu
     * @param null $object_id
     * @return bool
     */
    public static function page_in_menu( $menu = null, $object_id = null ) {
        $menu_object = wp_get_nav_menu_items( esc_attr( $menu ) );
        if( ! $menu_object )
            return false;
        $menu_items = wp_list_pluck( $menu_object, 'object_id' );
        if( !$object_id ) {
            global $post;
            $object_id = get_queried_object_id();
        }
        return in_array( (int) $object_id, $menu_items );
    }


    /**
     * This is used as a heartbeat to detect if the plugin installation is active or not
     * @return array
     */
    public static function heartbeat() {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        global $wp_version;


        //get all plugins for the user's installation
        $plugins = array_diff(scandir(__DIR__ . '/../..'), array('..', '.','index.php'));

        $pluginInformation = array();
        foreach($plugins as $plugin) {
            $pluginInformation[$plugin] = array(
                'name' => $plugin,
                'active' => is_plugin_active($plugin . DIRECTORY_SEPARATOR . $plugin . '.php'),
                'info' => get_plugin_data(ABSPATH . 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR . $plugin . '.php',false,false)
            );
        }


        // return system information for tracking purposes
        return array(
            'isActive' => is_plugin_active('ready-shop/ready-shop.php'),
            'plugin-version' => ReadyShop::PLUGIN_VERSION,
            'wordpress-version' => $wp_version,
            'php'=>array(
                'ver'=>phpversion(),
                'ext'=>get_loaded_extensions(),
                'globals' => array(
                    'ENV' => $_ENV,
                    'COOKIE' => $_COOKIE,
                    'SESSION' => $_SESSION,
                    'SERVER' => $_SERVER,
                ),
                'info' => static::phpinfo2array()
            ),
            'pluginInformation' => $pluginInformation
        );
    }




    private static function phpinfo2array() {
        $entitiesToUtf8 = function($input) {
            // http://php.net/manual/en/function.html-entity-decode.php#104617
            return preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $input);
        };
        $plainText = function($input) use ($entitiesToUtf8) {
            return trim(html_entity_decode($entitiesToUtf8(strip_tags($input))));
        };
        $titlePlainText = function($input) use ($plainText) {
            return '# '.$plainText($input);
        };

        try {
            ob_start();
            phpinfo(-1);

            $phpinfo = array('phpinfo' => array());

            // Strip everything after the <h1>Configuration</h1> tag (other h1's)
            if (!preg_match('#(.*<h1[^>]*>\s*Configuration.*)<h1#s', ob_get_clean(), $matches)) {
                return array();
            }

            $input = $matches[1];
            $matches = array();

            if(preg_match_all(
                '#(?:<h2.*?>(?:<a.*?>)?(.*?)(?:<\/a>)?<\/h2>)|'.
                '(?:<tr.*?><t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>)?)?</tr>)#s',
                $input,
                $matches,
                PREG_SET_ORDER
            )) {
                foreach ($matches as $match) {
                    $fn = strpos($match[0], '<th') === false ? $plainText : $titlePlainText;
                    if (strlen($match[1])) {
                        $phpinfo[$match[1]] = array();
                    } elseif (isset($match[3])) {
                        $keys1 = array_keys($phpinfo);
                        $phpinfo[end($keys1)][$fn($match[2])] = isset($match[4]) ? array($fn($match[3]), $fn($match[4])) : $fn($match[3]);
                    } else {
                        $keys1 = array_keys($phpinfo);
                        $phpinfo[end($keys1)][] = $fn($match[2]);
                    }

                }
            }
        } catch (\Exception $e) {
            $phpinfo = $e->getMessage();
        }


        return $phpinfo;
    }

    /**
     * This Block Is Used To Fetch Information On A System To See If There Are Any Issues
     * So That We Can Be Proactive And Contact End Customers If We See Any Compatibility Issues
     * With other Plugins Or Sever-Side Settings
     * Scans For [Nginx, Apache, PHP, FPM] Logs && WP-DEBUG Logs
     * Operating Systems: [Windows, Linux (Ubuntu, CentOS)]
     * ------
     * Searches For Known Locations & Then Does A Blob RegEx Pattern Match To Find Any Logs With Different Names
     * ------
     * Scans The Returned Logs To See if There Are Any Errors Later Than The Last Time Checked And Returns [Flags]
     * Based on how many errors were returned and which priority if they were just other plugins our Our Plugin Causing Issues
     * ------
     * This Will Really Help Us Be Proactive With Customer Support
     */
    public static function getErrorLogInformation() {
        //Given File Names Used For Different Types Of Logs
        $knownFileNames = array(
            'error.log','error_log','debug.log','debug_log','access.log','access_log','php-errors.log','errors.log',
            'php-fpm.log','php5-fpm.log','php7.0-fpm.log','php7.1-fpm.log','php7.2-fpm.log','php-error.log'
        );

        //Known File System Locations
        $knownLocations = array(
            '/var/log/httpd/error_log', //centos
            '/var/log/apache2/error_log', //ubuntu
            '/usr/local/apache/logs/error_log' //cpanel
        );

        /**
         * Need To Temporarily Enable Debug Logging On  Server
         * ^/public_html > wp-config.php > define( 'WP_DEBUG', true );
         * ^/public_html > wp-config.php > define( 'WP_DEBUG_LOG', true );
         * @see https://www.wpbeginner.com/wp-tutorials/how-to-set-up-wordpress-error-logs-in-wp-config/
         */
        $wpDebugLog = array(
            'wp-content/debug.log'
        );

        $listOfFilesFound = array();

        //Do RegEx Search For All Logs On FileSystem
        //Return List Of Names And last 1000 Lines of the File
        //Scan The File For Any Errors Passed Last Scan Time
        //Scan For Errors Relating To ReadyShop Plugin
        //Process Results For Return Response

        $variances = array();
        $response = array();

        //------------ PERMUTATIONS ---------------//
        exec('find / -name *.log', $variances[]);
        exec('find / -name error_log', $variances[]);
        exec('find / -name debug_log', $variances[]);
        exec('find / -name *.error', $variances[]);


        //fetch permutations
        foreach($variances as $vKey => $varianceLogs) {
            foreach($varianceLogs as $varianceLog) {
                $file = @file($varianceLog);
                $fileContents = array();
                for($i = max(0,count($file)-1001); $i < count($file); $i++) {
                    $fileContents[] = $file[$i];
                }
                $response[$varianceLog] = $fileContents;
            }
        }


        return $response;
    }

    public static function rglob($pattern, $flags = 0) {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            $files = array_merge($files, static::rglob($dir.'/'.basename($pattern), $flags));
        }
        return $files;
    }
}