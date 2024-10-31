<?php
/*
    Plugin Name: Ready Shop
    Plugin URI: https://wordpress.org/plugins/ready-shop/
    Version: 1.0.17
    Author: Daniel P. Baddeley JR <dan@zahalo.com>
    Author URI: https://www.readyshop.com
    Description: Empowers You To Engage Your Audience Like Never Before. tag: [ready_shop]
*/
/**
 * Online Validator Tools Used To Test Stability of The Plugin
 * @see https://plugintests.com/plugins/ready-shop/latest
 * @see https://coderisk.com/wp/plugin/ready-shop#html
 */
/** Autoload System Classes */
//spl_autoload_register(function ($class) {
//    require_once 'lib/' . $class . '.php';
//});

require_once 'lib/ReadyShop.php';
require_once 'lib/ReadyShop_EnvironmentChecks.php';
require_once 'lib/ReadyShop_PackageManager.php';
require_once 'lib/ReadyShop_ProductService.php';
require_once 'lib/ReadyShop_AnalyticService.php';
require_once 'lib/ReadyShop_SingleProductIncludeMacro.php';
require_once 'lib/ReadyShop_MetaTagService.php';
require_once 'lib/ReadyShop_SettingsService.php';
require_once 'lib/ReadyShop_UserService.php';

use ReadyShop\lib\ReadyShop as ReadyShop;
use ReadyShop\lib\ReadyShop_EnvironmentChecks as ReadyShop_EnvironmentChecks;
use ReadyShop\lib\ReadyShop_PackageManager as ReadyShop_PackageManager;
use ReadyShop\lib\ReadyShop_ProductService as ReadyShop_ProductService;
use ReadyShop\lib\ReadyShop_AnalyticService as ReadyShop_AnalyticService;
use ReadyShop\lib\ReadyShop_SingleProductIncludeMacro as ReadyShop_SingleProductIncludeMacro;
use ReadyShop\lib\ReadyShop_MetaTagService as ReadyShop_MetaTagService;
use ReadyShop\lib\ReadyShop_SettingsService as ReadyShop_SettingsService;
use ReadyShop\lib\ReadyShop_UserService as ReadyShop_UserService;

error_reporting(E_ERROR | E_WARNING | E_PARSE);

//----------------------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------------
//      <(-_-)> WORD-PRESS CODE LOGIC START --- BAD PRACTICE WITH LINEAR CODE DEFINITIONS BUT WHATEVER... <(-_-)>
//----------------------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------------
header("Access-Control-Allow-Origin: *");//todo need to add check here to make sure it never adds this setting twice - or will cause 500 server errors for loading resources.
#
########################################################################################################################
#

//@todo Below Disabled As Remote Information About [View Details] Is Now Being Pulled From Wordpress Repository
//add_action( 'init', 'readyshop_activate_au' );
function readyshop_activate_au()
{
    require_once('readyshop_wp_autoupdate.php');
    $plugin_current_version = ReadyShop::PLUGIN_VERSION;
    $plugin_remote_path = ReadyShop::HOST_DOMAIN_SECURED . '/wp/update/' . ReadyShop_PackageManager::getApplicationToken();
    $plugin_slug = plugin_basename( __FILE__ );
    $license_user = ReadyShop_PackageManager::getApplicationToken();
    $license_key = ReadyShop_PackageManager::getApplicationToken();
    new ReadyShop_WP_AutoUpdate ( $plugin_current_version, $plugin_remote_path, $plugin_slug, $license_user, $license_key );
}


# @todo Find Out Why Token Would Be Disappearing In The First Place
add_action('init','readyshop_check_installation_token');
function readyshop_check_installation_token () {
    if(ReadyShop_PackageManager::getApplicationToken() == '' || ReadyShop_PackageManager::getApplicationToken() == 'null') {
        ReadyShop_PackageManager::activation();
    }
}




#
########################################################################################################################
#
function readyshop_add_facebook_meta_tags() {
    if(isset($_GET['pid']) || isset($_GET['rs_id'])) {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $requestURI = $_SERVER['REQUEST_URI'];
        $requestURI = explode('?', $requestURI);
        $requestURI = $requestURI[0];

        $id = (isset($_GET['pid']) && $_GET['pid'] != '' && is_int((int) $_GET['pid'])) ? $_GET['pid'] : (isset($_GET['rs_id']) && $_GET['rs_id'] != '' && is_int((int) $_GET['rs_id'])) ? $_GET['rs_id'] : null;

        $hostname = $protocol . $host . $requestURI;
        if($id !== null) {
            echo ReadyShop_MetaTagService::getFacebookMetaData($id, array('brand'=>'*','category'=>'*','color'=>'*'), $hostname);
            echo ReadyShop_MetaTagService::getTwitterMetaData($id, array('brand'=>'*','category'=>'*','color'=>'*'), $hostname);
        }

    }
}
//readyshop_add_facebook_meta_tags();
//add_action('wp_head','readyshop_add_facebook_meta_tags');
add_action('wp_enqueue_scripts','readyshop_add_facebook_meta_tags');


/**
 * [ready_shop] ShortCode
 *
 * @param $atts
 * @return mixed|string
 */
function readyshop_store_shortcode( $atts ){
    /**
     * Assuming The Page This Is running On Is Their Shop Page.
     * todo add_post_meta($post_id, $meta_key, $meta_value, $unique);
     *
     * Add Facebook Meta Information To Header Tags If $_GET['pid'] isset & === (int)
     *
    <meta property="og:url" content="https://pauladeen.shop/p/?id={{ product.id }}" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="{{ product.title }}" />
    <meta property="og:description" content="{{ product.description|length > 300 ? product.description|striptags|slice(0, 300) ~ '…' : product.description|striptags }}" />
    <meta property="og:image" content="{{ product.localGallery[0] }}" />
    <meta property="og:image:height" content="300" />
    <meta property="og:image:width" content="300" />
    <meta property="fb:app_id" content="272712689763612" />
    <!-- Author -->
    <meta property="article:author" content="https://www.facebook.com/PaulaDeen/" />
    <!-- Publisher -->
    <meta property="article:publisher" content="https://www.facebook.com/PaulaDeen/" />
    {#<meta property="fb:admins" content="100000073822702"/>#}
     */






    $appToken = ReadyShop_PackageManager::getApplicationToken();
    $a = shortcode_atts( array(
        'force_full_size' => 'false',
        'type' => 'embed', // optional : script || embed || iframe
        'height' => '100%', // optional :
        'width' => '100%' // optional
    ), $atts );

    $dic = new ReadyShop($a,$appToken);
    return $dic->execute();
}

/**
 * Attach Function To Framework
 * @param string : Template Tag Information
 * @param string : Bound Function Name
 */
add_shortcode( 'ready_shop', 'readyshop_store_shortcode' );

/**
 * Register Event Listeners For Activating & Deactivating The System Plugin
 */
function readyshop_activate() {
    ReadyShop_PackageManager::activation();
}

function readyshop_deactivate() {
    ReadyShop_PackageManager::deactivation();
}
register_activation_hook( __FILE__, 'readyshop_activate' );
register_deactivation_hook(__FILE__,'readyshop_deactivate' );

function readyshop_activation_redirect( $plugin ) {
    if( $plugin == plugin_basename( __FILE__ ) ) {
        if(ReadyShop::REGISTRATION_PROCESS_ACTIVE) {
            exit( wp_redirect( admin_url( 'admin.php?page=readyshop.setup' ) ) );
        } else {
            //exit( wp_redirect( admin_url( 'admin.php?page=readyshop.setup' ) ) );
        }
    }
}
add_action( 'activated_plugin', 'readyshop_activation_redirect' );

/**
 * Display Welcome Screen If Registration Process Is Inactive
 */
if(ReadyShop::REGISTRATION_PROCESS_ACTIVE === false) {
    register_activation_hook( __FILE__, 'fx_admin_notice_example_activation_hook' );

    function fx_admin_notice_example_activation_hook() {
        set_transient( 'fx-admin-notice-example', true, 5 );
    }

    add_action( 'admin_notices', 'fx_admin_notice_example_notice' );

    function fx_admin_notice_example_notice(){

        /* Check transient, if available display notice */
        if( get_transient( 'fx-admin-notice-example' ) ){
            ?>
            <div class="updated notice is-dismissible">
                <h1>Thank you for using Ready Shop!</h1>
                <p>Take a few simple steps to complete store setup</p>
                <br>
                <a href="admin.php?page=readyshop.products" class="button button-primary">Manage Products</a>
                <a href="admin.php?page=readyshop.home" class="button button-primary">View Your Dashboard</a>
                <a href="admin.php?page=readyshop.account" class="button button-primary">Setup Your Account</a>
                <a href="admin.php?page=readyshop.help" class="button button-primary" style="margin-bottom: 20px;">Help Center</a>
            </div>
            <?php
            /* Delete transient, only display this notice once. */
            delete_transient( 'fx-admin-notice-example' );
        }
    }
}

add_filter( 'plugin_action_links', 'ttt_wpmdr_add_action_plugin', 10, 5 );
function ttt_wpmdr_add_action_plugin( $actions, $plugin_file )
{
    static $plugin;

    if (!isset($plugin))
        $plugin = plugin_basename(__FILE__);
    if ($plugin == $plugin_file) {

        $manageProducts = array('products' => '<a href="'.admin_url( 'admin.php?page=readyshop.products' ).'">' . __('Manage Products', 'Products') . '</a>');
        $account = array('account' => '<a href="'.admin_url( 'admin.php?page=readyshop.account' ).'">Account</a>');
        $support = array('support' => '<a href="https://readyshop.zendesk.com/hc/en-us" target="_blank">Help Center</a>');

        $actions = array_merge($manageProducts, $actions);
        $actions = array_merge($account, $actions);
        $actions = array_merge($support, $actions);

    }

    return $actions;
}


#
########################################################################################################################
#
/**
 * [readyshop_product] ShortCode
 * @param $attributes
 * @return mixed|string
 */
function readyshop_product_shortcode( $attributes ){
    //include font-awesome icons
    wp_register_style('readyshop_font_awesome_icons', plugins_url('plugins/fontawesome/releases/v5.0.10/css/all.css', __FILE__));
    wp_enqueue_style('readyshop_font_awesome_icons');

    // TOASTER Notifications
    wp_register_style('readyshop_prefix_toastr', plugins_url('plugins/toastr/toastr.min.css', __FILE__));
    wp_enqueue_style('readyshop_prefix_toastr');
//    wp_register_script('prefix_jquery', plugins_url('plugins/toastr/toastr.min.js', __FILE__));
//    wp_enqueue_script('prefix_jquery');


    $appToken = ReadyShop_PackageManager::getApplicationToken();
    $a = shortcode_atts( array(
        'force_full_size' => 'false',
        'type' => 'embed', // optional : script || embed || iframe
        'id' => 'no-id'
    ), $attributes );

    $rsm = new ReadyShop_SingleProductIncludeMacro($a, $appToken);
    return $rsm->getMacroContent();
}

/**
 * Attach Function To Framework
 * @param string : Template Tag Information
 * @param string : Bound Function Name
 */
add_shortcode( 'readyshop_product', 'readyshop_product_shortcode' );



#
########################################################################################################################
#

//Will automatically register & enqueue assets
add_action( 'wp_enqueue_scripts', 'readyshop_universal_footer_cart' );

function readyshop_universal_footer_cart($hook) {
    //html

    //css
    wp_register_style('readyshop_styles', plugins_url('css/style.css?v='. ReadyShop::PLUGIN_VERSION, __FILE__));
    wp_enqueue_style('readyshop_styles');

    //js
    wp_enqueue_script('jquery');
    wp_register_script('readyshop_scripts', plugins_url('js/UniversalCart.js?v='.ReadyShop::PLUGIN_VERSION, __FILE__));
    wp_enqueue_script('readyshop_scripts');


//    rs_include_twitter_bootstrap();
    ?>

    <!-- the cart & everything in it -->
    <div class="rs-include" style="cursor:pointer;">
        <div id="cart">
            <section id="cart-tab">
                <span class="fa fa-shopping-cart fa-lg"></span>
                <label style="display:inline-block !important; cursor: pointer;">&nbsp;&nbsp;Shopping Cart</label>
                <span class="fa fa-angle-up fa-2x" id="show-cart-icon"></span>
<!--                <span class="fa fa-angle-down fa-2x" id="hide-cart-icon" style="display: none;"></span>-->
            </section>

            <section id="cart-body">
                <center>Add A Product To Your Cart!</center>
            </section>

            <section id="cart-footer">
                <div id="cart-subtotal"><small>Subtotal: $</small><span class="rs-uni-cart-subtotal">0.00</span></div>
                <br />
                <a class="rs-button" id="check-out-button" href="javascript:;">Check Out</a>
            </section>
        </div>
    </div>

    <?php
}

#
########################################################################################################################
#

#Add A Dashboard The Affiliates Can See
#@todo Integrate Into Better Object Oriented Standards
/**
 * @link https://developer.wordpress.org/reference/functions/add_menu_page/#
 * @link https://codex.wordpress.org/Roles_and_Capabilities
 */
function readyshop_include_twitter_bootstrap()
{
    // Adds Stateless Application Token To Client
    echo '<script type="text/javascript">localStorage.setItem("application_token","'.ReadyShop_PackageManager::getApplicationToken().'");</script>';



    // JQUERY
//    wp_register_script('prefix_toastr', 'https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js');
//    wp_enqueue_script('prefix_toastr');
    wp_enqueue_script('jquery');

    // TOASTER Notifications
    wp_register_style('readyshop_prefix_toastr_style', plugins_url('plugins/toastr/toastr.min.css', __FILE__));
    wp_enqueue_style('readyshop_prefix_toastr_style');
    wp_register_script('readyshop_prefix_toastr_script', plugins_url('plugins/toastr/toastr.min.js', __FILE__));
    wp_enqueue_script('readyshop_prefix_toastr_script');



    // POPPER.JS
    wp_register_script('readyshop_prefix_popper', plugins_url('plugins/popper/releases/1.11.0/umd/popper.min.js', __FILE__));
    wp_enqueue_script('readyshop_prefix_popper');

    // JS
    wp_register_script('readyshop_prefix_bootstrap_script', plugins_url('plugins/bootstrap/releases/4.0.0-beta/js/bootstrap.min.js', __FILE__));
    wp_enqueue_script('readyshop_prefix_bootstrap_script');

    // CSS
    wp_register_style('readyshop_prefix_bootstrap_style', plugins_url('plugins/bootstrap/releases/4.0.0-beta/css/bootstrap.min.css', __FILE__));
    wp_enqueue_style('readyshop_prefix_bootstrap_style');
}



function readyshop_include_product_activation_management() {

    /*Register Scripts */
    //wp_register_script('readyshop_blockUIScript', plugins_url('plugins/jquery/jquery.blockUI.js', __FILE__));
    wp_register_script('readyshop_sortElementsScript', plugins_url('js/module/SortElements.js?v='.ReadyShop::PLUGIN_VERSION, __FILE__));
    wp_register_script('readyshop_graphScript', plugins_url('js/ProductActivationManager.js?v='.ReadyShop::PLUGIN_VERSION, __FILE__));

    /*Include Scripts*/
    //wp_enqueue_script('readyshop_blockUIScript');
    wp_enqueue_script('jquery');
    wp_enqueue_script('readyshop_sortElementsScript');
    wp_enqueue_script('readyshop_graphScript');
}

function readyshop_include_settings_management() {
    wp_enqueue_script('jquery');
    wp_register_style('form_validation', plugins_url('css/FormValidation.css?v='.ReadyShop::PLUGIN_VERSION, __FILE__));
    wp_enqueue_style('form_validation');
    wp_register_script('readyshop_validate_js', plugins_url('plugins/jquery/validate/releases/1.17.0/jquery.validate.min.js', __FILE__));
    wp_enqueue_script('readyshop_validate_js');
    wp_register_script('readyshop_validate_additional_js', plugins_url('plugins/jquery/validate/releases/1.17.0/additional-methods.min.js', __FILE__));
    wp_enqueue_script('readyshop_validate_additional_js');
    wp_register_script('readyshop_management_script', plugins_url('js/SettingsManagement.js?v='.ReadyShop::PLUGIN_VERSION, __FILE__));
    wp_enqueue_script('readyshop_management_script');
}

function readyshop_include_setup_management() {
    wp_enqueue_script('jquery');
    wp_register_script('readyshop_setup_main', plugins_url('js/Setup.js?v='.ReadyShop::PLUGIN_VERSION,__FILE__));
    wp_enqueue_script('readyshop_setup_main');
}





function readyshop_affiliate_dash() {


    readyshop_include_twitter_bootstrap();

    $stats = ReadyShop_AnalyticService::getDashboardStats();
    $settings = ReadyShop_SettingsService::getSettings();



    ReadyShop_EnvironmentChecks::displayConfigurationAlerts();
    ?>

<!--    <div class="col-md-12" style="margin-top: 20px">-->
<!--        <ul class="breadcrumb">-->
<!--            <li class="breadcrumb-item"><a href="?page=readyshop">Dashboard</a></li>-->
<!--            <!--  <li class="active"><a href="?page=readyshop.settings">Settings</a></li>-->
<!--            <li class="breadcrumb-item active"><a href="?page=readyshop">Home</a></li>-->
<!--        </ul>-->
<!--    </div>-->
    <div class="container-fluid">
        <h1 class="display-4 mt-4 mb-4">Welcome, <?php if(isset($settings['firstName']) && $settings['firstName'] != '') { echo $settings['firstName']; } else { echo 'Influencer'; } ?></h1>
        <hr />
        <div class="row">
            <section class="col-md-4">
                <h2 class="mt-3">Overview</h2>
            </section>
        </div>
        <div class="row mt-1">
            <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-3 p-4">
                <label class="lead" data-toggle="tooltip" data-placement="top" title="How much commission is available to be withdrawn from this account.">Withdrawable Cash</label>
                <h1><small>$</small><span class="display-4"><?php echo number_format(explode('.',$stats['availableCommissions'])[0],0); ?></span><small>.<?php echo explode('.',number_format($stats['availableCommissions'],2))[1]; ?></small></h1>
                <label><small>Payment scheduled for <?php echo $stats['nextScheduledPayment']; ?></small></label>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-3 p-4">
                <label class="lead" data-toggle="tooltip" data-placement="top" title="How much commission has been made including [Shipping Fees].">Pending Commissions</label>
                <h1><small>$</small><span class="display-4"><?php echo number_format(explode('.',$stats['pendingCommissions'])[0],0); ?></span><small>.<?php echo explode('.',number_format($stats['pendingCommissions'],2))[1]; ?></small></h1>
                <label><small>Last 30 days</small></label>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-3 p-4">
                <label class="lead" data-toggle="tooltip" data-placement="top" title="How many products have been sold in the current billing period.">Products Sold</label>
                <h1><span class="display-4"><?php echo $stats['productsSold']; ?></span></h1>
                <label><small>Last 30 days</small></label>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-3 p-4">
                <label class="lead" data-toggle="tooltip" data-placement="top" title="Total sales since registration date.">Lifetime Commission</label>
                <!--                <h1><small>$</small><span class="display-4">--><?php //echo number_format(explode('.',$stats['lifetimeSales'])[0],0); ?><!--</span><small>.--><?php //echo explode('.',$stats['lifetimeSales'])[1]; ?><!--</small></h1>-->
                <h1><small>$</small><span class="display-4"><?php echo number_format(explode('.',$stats['lifetimeSales'])[0],0); ?></span><small>.<?php echo explode('.',number_format($stats['lifetimeSales'],2))[1]; ?></small></h1>
                <label><small>Since <?php echo $stats['accountCreated']; ?></small></label>
            </div>
        </div>
        <hr />
        <p class="alert alert-info"><strong>Don't worry!</strong> Commission Payments are made on the <b>1st</b> & <b>15th</b> of <b>Every Month</b>!</p>

        <div class="row">
            <section class="col-md-4">
                <h2 class="mt-3">Activity</h2>
            </section>
        </div>
        <div class="row mt-1">
            <table class="table mt-3">
                <thead class="thead-default">
                <tr>
                    <th>Product</th>
                    <th class="text-center">Date</th>
                    <th class="text-center">Commission</th>
                    <th class="text-center">Status</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($stats['productInformation'] as $product) {
                    ?>
                    <tr>
                        <th scope="row"><img src="<?php echo $product['image']; ?>" style="height: 75px; width: 75px;"> <?php echo  $product['name']; ?></th>
                        <td class="text-center">
                            <small><?php echo $product['date']; ?></small>
                        </td>
                        <td class="text-center"  data-toggle="tooltip" data-placement="top" title="How much commission has been made on this product line.">
                            <small>$</small>
                            <?php echo number_format($product['commission'],2); ?>
                        </td>
                        <td>
                            <?php
                            if($product['status'] === 'pending'){
                                ?>
                                <div class="text-center border border-secondary rounded p-1" data-toggle="tooltip"
                                     title="Holding payment until return period has passed."> Pending
                                </div>
                                <?php
                            } else if($product['status'] === 'cleared') {

                                ?>
                                <div class="text-center text-white bg-info rounded p-1" data-toggle="tooltip" title="Scheduled for next pay out.">Cleared</div>
                                <?php
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                <!--                <tr>-->
                <!--                    <th scope="row">Paula Deen Signature Collection Porcelain Nonstick 15Pc Cookware Set</th>-->
                <!--                    <td class="text-center"><small>MM/DD/YYYY</small></td>-->
                <!--                    <td class="text-center"><small>$</small>8.90</td>-->
                <!--                    <td><div class="text-center text-white bg-info rounded p-1" data-toggle="tooltip" title="Scheduled for next pay out.">Cleared</div></td>-->
                <!--                </tr>-->
                <!--                <tr>-->
                <!--                    <th scope="row">Paula Deen Signature Collection Porcelain Nonstick 15Pc Cookware Set</th>-->
                <!--                    <td class="text-center"><small>MM/DD/YYYY</small></td>-->
                <!--                    <td class="text-center"><small>$</small>8.90</td>-->
                <!--                    <td><div class="text-center bg-success rounded text-white p-1" data-toggle="tooltip" title="Paid on MM/DD/YYYY.">Paid</div></td>-->
                <!--                </tr>-->
                <!--                <tr>-->
                <!--                    <th scope="row">Paula Deen Signature Collection Porcelain Nonstick 15Pc Cookware Set</th>-->
                <!--                    <td class="text-center"><small>MM/DD/YYYY</small></td>-->
                <!--                    <td class="text-center"><small>$</small>8.90</td>-->
                <!--                    <td><div class="text-center text-white bg-danger rounded p-1" data-toggle="tooltip" title="Item has been returned, commission voided.">Returned</div></td>-->
                <!--                </tr>-->
                </tbody>
            </table>
        </div>
    </div>


    <!--<script type="application/javascript">
        var $ = jQuery;
    </script>-->

    <?php

//    wp_register_style('graphStyles', '//api.readyshop.com/assets/css/OrderBreakdowns/GraphStyles.css');
//    wp_enqueue_style('graphStyles');
//    wp_register_script('graphPlugin', '//api.readyshop.com/assets/plugins/echarts/echarts.js');
//    wp_enqueue_script('graphPlugin');
//    wp_register_script('graphScript', '//api.readyshop.com/assets/js/WordpressPlugin/GlobalSales.js');
//    wp_enqueue_script('graphScript');
    readyshop_help_desk();
}


function readyshop_displayCategories($categories) {
    $string = '';
    foreach($categories as $key => $category) {
        $string .= '.'.str_replace(',','-',str_replace(' ','-',str_replace(' & ','-and-',$category))).' ';
    }
    return strtolower($string);
}

function readyshop_affiliate_products($internal = false){
//    wp_register_script('readyshop_pace_js', plugins_url('plugins/pace/pace.min.js', __FILE__));
//    wp_enqueue_script('readyshop_pace_js');

//    $products = ReadyShop_ProductService::getProducts();
//    $listedProducts = ReadyShop_ProductService::getAddedProducts();
    $listedCategories = ReadyShop_ProductService::getCategories();
    $listedBrands = ReadyShop_ProductService::getBrands();
//    $listedCategoriesByBrand = ReadyShop_ProductService::getCategoriesByBrand();
    ?>
    <style type="text/css">
        /** @todo I Want These Associations Removed **/
        .dropdown-menu {
            /* position: absolute; */
            /* will-change: transform; */
            /* left: 0px; */
            /* transform: translate3d(0px, -133px, 0px); */
            /* top: 0px; */
        }

        /*#finish-product-selection {*/
        /*position: fixed;*/
        /*z-index: 1000;*/
        /*border-radius: 100px;*/
        /*float: right;*/
        /*margin-left: 82vw;*/
        /*!*margin-top: -250px;*!*/
        /*-webkit-box-shadow: 0px 0px 34px 0px rgba(0,161,24,1);*/
        /*-moz-box-shadow: 0px 0px 34px 0px rgba(0,161,24,1);*/
        /*box-shadow: 0px 0px 34px 0px rgba(0,161,24,1);*/
        /*}*/
    </style>

    <?php if($internal): ?>
        <!--        <button type="submit" class="btn btn-success btn-lg"  id="finish-product-selection" style="cursor: pointer;">Finish Step</button>-->
    <?php endif; ?>

    <?php if(!$internal): ?>
        <div class="container-fluid mt-4">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="?page=readyshop">Dashboard</a></li>
                <!--              <li class="active"><a href="?page=readyshop.settings">Settings</a></li>-->
                <li class="breadcrumb-item active"><a href="?page=readyshop">Products</a></li>
            </ul>
        </div>
    <?php endif; ?>

    <style type="text/css">
        #product-filter-navigation {
            position: fixed;
            top: 0;
            padding-top: 20px;
            z-index: 500;
            background: #FFFFFF;
        }
        .container-fluid {
            opacity: 0.00;
        }
        /** This is a global override due to the [transform: translate3d] Boostrap DropDown Issue **/
        body {
            height: auto !important;
        }
    </style>
    <script type="text/javascript">
        // Pace.start();
        // Pace.on('done',function() {
        //     $('.container-fluid').css('opacity',1.0);
        // })
        document.addEventListener('DOMContentLoaded',function(event) {
            jQuery('.container-fluid').css('opacity',1.0);
        })
    </script>


    <div id="product-filter-navigation" class="container-fluid mt-4">
        <div class="row">
            <h1 class="col-md-3">Manage Products</h1>
            <div class="col-lg-9">
                <div class="input-group mt-1">
                    <div class="input-group-btn">
                        <a href="javascript:;" class="product-selection-add-all-displayed btn btn-secondary bg-light text-dark" aria-expanded="false">Add All Products</a>
                        <a href="javascript:;" class="product-selection-remove-all-displayed btn btn-secondary bg-light text-dark" aria-expanded="false">Remove All Products</a>
                        <button type="button" class="product-selection-dropdown-filters btn btn-secondary dropdown-toggle bg-light text-dark" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Filters
                        </button>
                        <div class="dropdown-menu">
                            <!--
                               todo: If unselected this should perform a "Random" Sort Operation To Change The Screen Display To Not-Confuse People
                            -->
                            <a class="dropdown-item" data-selection=".added-product .removed-product" href="#">All</a>
                            <a class="dropdown-item" data-selection=".added-product" href="#">Added</a>
                            <a class="dropdown-item" data-selection=".removed-product" href="#">Not Added</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" data-selection=".decending-commission" href="#"><i class="fa fa-dollar"></i> Most Commission</a>
                            <a class="dropdown-item" data-selection=".ascending-commission" href="#"><i class="fa fa-dollar"></i> Least Commission</a>
                        </div>
                    </div>

                    <!-- Brands Navigation -->
                    <div class="input-group-btn">
                        <button type="button" class="brand-selection-dropdown btn btn-secondary dropdown-toggle bg-light text-dark" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            All Brands
                        </button>
                        <div class="dropdown-menu" style="height:auto; max-height: 400px; overflow-x: hidden;">
                            <a class="dropdown-item" data-brand="true" data-selection="all" href="javascript:;" title="All Brands">All Brands</a>
                            <div role="separator" class="dropdown-divider"></div>
                            <?php
                            sort($listedBrands);
                            for($i = 0; $i < count($listedBrands); $i++) {
                                echo '<a class="dropdown-item" data-brand="true" data-selection=".'.strtolower(str_replace( ' ', '-', str_replace(' & ', '-and-',$listedBrands[$i]))).'" title="'.$listedBrands[$i].'" href="javascript:;">'.$listedBrands[$i].'</a>';
                            }
                            ?>
                        </div>
                    </div>


                    <!-- Categories Navigation -->
                    <div class="input-group-btn category-selection-all" style="display:block;">
                        <button type="button" class="product-selection-dropdown btn btn-secondary dropdown-toggle bg-light text-dark" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            All Categories
                        </button>
                        <div class="dropdown-menu" style="height:auto; max-height: 400px; overflow-x: hidden;">
                            <a class="dropdown-item" data-category="true" data-selection="all" href="#" title="All Categories">All Categories</a>

                            <div role="separator" class="dropdown-divider"></div>
                            <?php
                            sort($listedCategories);
                            for($i = 0; $i < count($listedCategories); $i++) {
                                echo '<a class="dropdown-item" data-category="true" data-selection=".'.strtolower(str_replace(' ', '-', str_replace(' & ','-and-',$listedCategories[$i]))).'" title="'.$listedCategories[$i].'" href="javascript:;">'.$listedCategories[$i].'</a>';
                            }
                            ?>
                        </div>
                    </div>


                    <!-- Brand Level Category Selection -->
                    <div class="input-group-btn category-selection-by-brand" style="display: none;">

                    </div>



                    <input type="text" id="product-listings-search" class="form-control" aria-label="Text input with dropdown button" placeholder="Search all products">
                </div>
            </div>
        </div>
        <?php if($internal): ?>
            <p class="alert alert-info" style="font-size: 17px;">After selecting the products for your store <a id="finish-product-selection" href="javascript:;">Click Here To Finish Setup</a>!</p>
        <?php endif; ?>
        <hr />
    </div>

    <div class="row" style="margin-top: 20px;">
        <div class="col-md-12">
            <div class="alert alert-info">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <strong>Info!</strong> To add or remove single products click add or remove. To add or remove products in mass click add all/remove all products buttons
            </div>
        </div>
        <div class="col-md-12">
            <div class="alert alert-warning">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <strong>Help!</strong> [Add|Remove All Products] Buttons Work Based On What [Brand | Category | Search Keywords] You Have Selected
            </div>
        </div>
    </div>

    <div class="container-fluid">

        <!-- END OF MODAL : Product Details -->
        <div class="row" id="product-listing-container" style="margin-bottom: 20px;">
            <div id="default-product-loading-animation" style="margin-left: 40%">
                <script type="text/javascript">
                    localStorage.setItem('rs_loading_animation', "<?php echo plugin_dir_url(__FILE__) . 'images/cart-1.5s-200px.gif'; ?>");
                    localStorage.setItem('rs_404_animation', "<?php echo plugin_dir_url(__FILE__) . 'images/no-record-found.png'; ?>");
                </script>
                <img src="<?php echo plugin_dir_url(__FILE__) . 'images/cart-1.5s-200px.gif'; ?>">
                <p>Loading List Of Products, Please Wait...</p>
            </div>



            <!-- END OF ALL PRODUCT LISTINGS -->
        </div>
    </div>



    <?php readyshop_help_desk(); ?>


<!--    <script>-->
<!--        function init() {-->
<!--            var imgDefer = document.getElementsByTagName('img');-->
<!--            for (var i=0; i<imgDefer.length; i++) {-->
<!--                if(imgDefer[i].getAttribute('data-src')) {-->
<!--                    imgDefer[i].setAttribute('src',imgDefer[i].getAttribute('data-src'));-->
<!--                } } }-->
<!--        window.onload = init;-->
<!--    </script>-->




    <?php
    if(!$internal) {
        readyshop_include_twitter_bootstrap();
    }
    readyshop_include_product_activation_management();
}
function readyshop_affiliate_account($internal = false){
    readyshop_include_twitter_bootstrap();
    readyshop_include_settings_management();

    //@todo fetching settings must be contingent on wither or not the user is logged in
    $settings = ReadyShop_SettingsService::getSettings();

    //check to see wither or not the user is logged in
    $loggedIn = ReadyShop_UserService::isLoggedIn();


    ?>
    <style type="text/css">
        .form-control.error {
            border: 1px solid red !important;
        }
    </style>


    <?php

    if($internal === false):
        ?>
        <div class="container-fluid mt-4">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="?page=readyshop">Dashboard</a></li>
                <!--              <li class="active"><a href="?page=readyshop.settings">Settings</a></li>-->
                <li class="breadcrumb-item active"><a href="?page=readyshop">Account</a></li>
            </ul>
        </div>
    <?php endif; ?>

    <div class="container-fluid">
        <div class="row">
            <?php if($internal === false): ?>
                <h1>Account</h1>
            <?php elseif ($internal === true): ?>
                <h2 style="margin-left: 10px;">Login</h2>
            <?php endif; ?>
            <div class="col-md-6 col-sm-4 hidden-xs"></div>

            <?php
            if(!$loggedIn):
                ?>
                <div class="form-group col-md-2" style="">
                    <!--                <label for="password1" class="col-form-label">Password</label>-->
                    <input type="text" class="form-control" id="loginEmail" name="loginEmail" placeholder="john.smith@gmail.com">
                </div>
                <div class="form-group col-md-2" style="">
                    <!--                <label for="password2" class="col-form-label">Repeat Password</label>-->
                    <input type="password" class="form-control" id="loginPassword" name="loginPassword" placeholder="l!pBHt%Chto1jR">
                </div>
                <a href="javascript:;" id="readyshop-login-form" class="btn btn-primary btn-lg" style="height: 38px; padding: .1rem 1rem;">Login</a>
            <?php
            endif;
            ?>
            <?php
            if($loggedIn) {
                echo '<div class="col-md-2"></div><div class="col-md-2" style="text-align: center; padding: 8px;">'.$settings['email'].'</div>';
                echo '<a href="javascript:;" id="readyshop-logout-action" class="btn btn-danger btn-lg" style="height: 38px; padding: .1rem 1rem;" aria-disabled="true" disabled>Logout</a>';
            }
            ?>
        </div>
        <hr />
    </div>
    <div class="container-fluid">
        <div class="row mt-5 mb-2">
            <?php if(!$internal): ?>
                <h2 class="col-sm-12 col-lg-2">Profile</h2>
            <?php elseif ($internal): ?>
                <h2 class="col-sm-12 col-lg-2">Register</h2>
            <?php endif; ?>
            <h4 class="col-sm-12 col-lg-10">General Information</h4>
        </div>
        <div class="row">
            <div class="col-sm-12 col-lg-2"></div>
            <div class="col-sm-12 col-lg-8">
                <p class="alert alert-info"><strong>Info!</strong> For security reasons we currently only allow <b>1</b> user account per plugin installation.</p>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-sm-12 col-lg-2"><!--Blank column for alignment--></div>
            <form class="col-sm-12 col-lg-10" id="settings-profile">
                <div class="row">
                    <div class="form-group col-md-5">
                        <label for="exampleInputEmail1">First name</label>
                        <input type="text" class="form-control" id="firstName" name="firstName" aria-describedby="" placeholder="Johnny" value="<?php echo ($loggedIn) ? $settings['firstName'] : ''; ?>">
                    </div>
                    <div class="form-group col-md-5">
                        <label for="exampleInputEmail1">Last name</label>
                        <input type="text" class="form-control" id="lastName" name="lastName" aria-describedby="" placeholder="Appleseed" value="<?php echo ($loggedIn) ? $settings['lastName'] : ''; ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-5">
                        <label for="exampleInputEmail1">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp" placeholder="Enter email" value="<?php echo ($loggedIn) ? $settings['email'] : ''; ?>" <?php if($loggedIn && $settings['email'] != ''): echo 'disabled'; endif; ?>>
                        <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
                    </div>
                    <div class="form-group col-md-5">
                        <label class="form-check-label mt-4 pt-3 pl-0">
                            <input class="form-check-input" type="checkbox" id="inlineCheckbox1" name="inlineCheckbox1" value="agreed-email" <?php if($loggedIn && $settings['inlineCheckbox1'] != ''): echo 'checked'; endif; ?>>
                            Receive updates via email
                        </label>
                    </div>
                </div>
                <?php if(!$loggedIn): ?>
                    <div class="row">
                        <div class="form-group col-md-5">
                            <label for="password1" class="col-form-label">Password</label>
                            <input type="text" class="form-control" id="password1" name="password1" placeholder="l!pBHt%Chto1jR">
                        </div>
                        <div class="form-group col-md-5">
                            <label for="password2" class="col-form-label">Repeat Password</label>
                            <input type="text" class="form-control" id="password2" name="password2" placeholder="l!pBHt%Chto1jR">
                        </div>
                    </div>
                <?php endif; ?>
                <div class="row">
                    <div class="form-group col-md-5">
                        <label for="address1" class="col-form-label">Street address</label>
                        <input type="text" class="form-control" id="address1" name="address1" placeholder="Street Address"  value="<?php echo ($loggedIn) ? $settings['address1'] : ''; ?>">
                    </div>
                    <div class="form-group col-md-5">
                        <label for="address2" class="col-form-label">Apartment, studio, or floor</label>
                        <input type="text" class="form-control" id="address2" name="address2" placeholder="Apartment, studio, or floor" value="<?php echo ($loggedIn) ? $settings['address2'] : ''; ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-5">
                        <label for="city" class="col-form-label">City</label>
                        <input type="text" class="form-control" id="city" name="city" placeholder="Tampa" value="<?php echo ($loggedIn) ? $settings['city'] : ''; ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="state" class="col-form-label">State</label>
                        <select id="state" name="state" class="form-control required">
                            <option value="">--</option>
                            <?php
                            foreach(ReadyShop::states() as $key => $value) {
                                if($key === $settings['state']) {
                                    echo '<option value="' . $key . '" selected>' . $value . '</option>';
                                } else {
                                    echo '<option value="' . $key . '">' . $value . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="zipcode" class="col-form-label">Zip</label>
                        <input type="text" class="form-control" id="zipcode" name="zipcode" placeholder="90210" value="<?php echo ($loggedIn) ? $settings['zipcode'] : ''; ?>">
                    </div>
                </div>
                <div class="row mt-5 mb-2">
                    <div class="col-md-10">
                        <?php if($loggedIn && !$internal || !$loggedIn): ?>
                            <button type="submit" class="btn btn-primary btn-lg"  id="submit-settings" style="cursor: pointer;"><?php echo ($loggedIn) ? 'Update' : 'Register'; ?></button>
                        <?php endif; ?>
                        <?php if($loggedIn && $internal): ?>
                            <button type="submit" class="btn btn-success btn-lg"  id="finish-profile-creation" style="cursor: pointer;">Finish</button>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
        <hr />
    </div>

    <?php if($loggedIn && ReadyShop_SettingsService::checkRegistrationStatus($settings)): ?>

        <div class="container-fluid">
        <div class="row mt-5 mb-2">
            <h2 class="col-sm-12 col-lg-2">Payment</h2>
            <h4 class="col-sm-12 col-lg-10">Payment Accounts</h4>
        </div>
        <div class="row mt-3 mb-5">
            <div class="col-sm-12 col-lg-2">
                <!--Blank column for alignment-->
            </div>
            <div class="col-sm-12 col-lg-10">
                <form id="paypal-payment-form">
                    <div class="row">
                        <!--                    <div class="col-md-5">-->
                        <!--                        <a class="btn btn-primary btn-lg btn-block" id="associate-paypal" href="#" role="button">Connect to PayPal</a>-->
                        <!--                    </div>-->
                        <div class="form-group col-md-5" id="paypal-email-container">
                            <label for="paypalEmail">Paypal Email</label>
                            <input type="email" class="form-control" id="paypalEmail" name="paypalEmail" aria-describedby="" placeholder="john.smith@gmail.com" value="<?php echo ($loggedIn) ? $settings['paypalEmail'] : ''; ?>">
                        </div>


                        <!--                    <div class="col-md-5">-->
                        <!--CONNECT TO BANK BUTTON
                        <a class="btn btn-primary btn-lg btn-block" href="#" role="button">Connect to Bank</a>
                        -->
                        <!--                    </div>-->
                    </div>
                    <div class="row mt-5 mb-2">
                        <div class="col-md-10">
                            <button type="submit" class="btn btn-primary btn-lg"  id="submit-paypal-email" style="cursor: pointer;"><?php echo ($loggedIn && $settings['paypalEmail'] != '') ? 'Update' : 'Save'; ?></button>
                            <?php if($internal): ?>
                                <button type="submit" class="btn btn-success btn-lg"  id="finish-paypal-creation" style="cursor: pointer;">Finish</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mt-5 mb-2">
                        <div class="col-md-10">
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <hr />

    <?php endif; ?>
    <?php if($loggedIn && ReadyShop_SettingsService::checkRegistrationStatus($settings) && ReadyShop_SettingsService::checkPaypalEmailForCommissionPayment($settings)): ?>

        <div class="row mt-5 mb-2">
            <h2 class="col-sm-12 col-lg-2">Form W-9</h2>
            <h4 class="col-sm-12 col-lg-9">Taxpayer Identification Number and Certification</h4>
        </div>
        <div class="row">
            <div class="col-sm-12 col-lg-2"></div>
            <div class="col-sm-12 col-lg-8">
                <p class="alert alert-info"><strong>Don't worry!</strong> We don't need all of this information just yet, this is required by the IRS for us to pay you any total greater than $600. If left blank, then when your total payments to date reach $600 the exceeding amount will be held within your account balance.</p>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-sm-12 col-lg-2"></div>
            <form class="col-sm-12 col-lg-10" id="rs-w9-form">
                <div class="row">
                    <div class="form-group col-md-5">
                        <label for="fullName">Name</label>
                        <input type="text" class="form-control" id="fullName" name="fullName" aria-describedby="emailHelp" placeholder="Johnny Appleseed" value="<?php echo ($loggedIn) ? $settings['fullName'] : ''; ?>">
                        <small id="emailHelp" class="form-text text-muted">Enter name as shown on income tax return.</small>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-5">
                        <label for="businessName">Business name (if different from above)</label>
                        <input type="text" class="form-control" id="businessName" name="businessName" aria-describedby="emailHelp" placeholder="AwesomeBlog" value="<?php echo ($loggedIn) ? $settings['businessName'] : ''; ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-5">
                        <label for="inputTaxClassification" class="col-form-label">Choose federal tax classification</label>
                        <select class="form-control" id="inputTaxClassification" name="inputTaxClassification">
                            <?php
                            foreach(ReadyShop::federalTaxClassifications() as $value) {
                                if($loggedIn && $settings['inputTaxClassification'] === $value) {
                                    echo '<option value="' . $value . '" selected>' . $value . '</option>';
                                } else {
                                    echo '<option value="' . $value . '">' . $value . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-5">
                        <label for="businessAddress1" class="col-form-label">Street address</label>
                        <input type="text" class="form-control" id="businessAddress1" name="businessAddress1" placeholder="Street Address" value="<?php echo ($loggedIn) ? $settings['businessAddress1'] : ''; ?>">
                    </div>
                    <div class="form-group col-md-5">
                        <label for="businessAddress2" class="col-form-label">Apartment, studio, or floor</label>
                        <input type="text" class="form-control" id="businessAddress2" name="businessAddress2" placeholder="Apartment, studio, or floor" value="<?php echo ($loggedIn) ? $settings['businessAddress2'] : ''; ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-5">
                        <label for="businessCity" class="col-form-label">City</label>
                        <input type="text" class="form-control" id="businessCity" name="businessCity" placeholder="Tampa" value="<?php echo ($loggedIn) ? $settings['businessCity'] : ''; ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="businessState" class="col-form-label">State</label>
                        <select id="businessState" name="businessState" class="form-control">
                            <option value="">--</option>
                            <?php
                            foreach(ReadyShop::states() as $key => $value) {
                                if($loggedIn && $settings['businessState'] === $key) {
                                    echo '<option value="' . $key . '" selected>' . $value . '</option>';
                                } else {
                                    echo '<option value="' . $key . '">' . $value . '</option>';
                                }
                            }
                            ?>
                        </select>

                    </div>
                    <div class="form-group col-md-3">
                        <label for="businessZipcode" class="col-form-label">Zip</label>
                        <input type="text" class="form-control" id="businessZipcode" name="businessZipcode" placeholder="33607" value="<?php echo ($loggedIn) ? $settings['businessZipcode'] : ''; ?>">
                    </div>
                </div>
                <div class="row mt-4 mb-2">
                    <h4 class="col-md-10">Taxpayer Identification Number (TIN)</h4>
                </div>
                <div class="row mb-2">
                    <div class="form-group col-md-5">
                        <label for="socialSecurity">Social security number (SSN)</label>
                        <input type="text" class="form-control" id="socialSecurity" name="socialSecurity" aria-describedby="" placeholder="###-##-####" value="<?php echo ($loggedIn) ? $settings['socialSecurity'] : ''; ?>">
                    </div>
                    <div class="form-group col-md-5">
                        <label for="employerIdentificationNumber">Employer identification number (EIN)</label>
                        <input type="text" class="form-control" id="employerIdentificationNumber" name="employerIdentificationNumber" aria-describedby="" placeholder="##-#######" value="<?php echo ($loggedIn) ? $settings['employerIdentificationNumber'] : ''; ?>">
                    </div>
                </div>
                <div class="row mt-4 mb-2">
                    <h4 class="col-md-10">Certification</h4>
                </div>
                <div class="row">
                    <div class="form-group col-md-10">
                        <label class="form-check-label pl-0">
                            <input class="form-check-input" type="checkbox" id="w9FormCertification" name="w9FormCertification" value="agreed-w9">
                            I certify that the required information has been filled out accurately, and that I am a U.S. citizen/other U.S. person
                        </label>
                    </div>
                </div>
                <div class="row mt-5 mb-2">
                    <div class="col-md-10">
                        <button type="submit" class="btn btn-primary btn-lg"  id="submit-tax-information" style="cursor: pointer;"><?php echo ($loggedIn) ? 'Update' : 'Save'; ?></button>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>
    </div>



    <?php
    readyshop_help_desk();
}

/**
 * Used To Display Registration | Setup Intake Process
 */
function readyshop_affiliate_setup() {
//    wp_register_style('readyshop_font_awesome_icons', plugins_url('plugins/fontawesome/releases/v5.0.10/css/all.css', __FILE__));
//    wp_enqueue_style('readyshop_font_awesome_icons');

    echo '<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp" crossorigin="anonymous">';



    ?>
        <?php
            if(!ReadyShop_PackageManager::checkRegistrationStep(0,'completed') || !ReadyShop_PackageManager::checkRegistrationStep(1,'completed')):
        ?>
    <style>
        #readyshop-setup-wizard-container {
            /*background-color: #f7fcff;*/
        }
    </style>
    <div id="readyshop-setup-wizard-container" class="row collapse show" style=" padding-top: 40px;">
        <div class="row col-md-12">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <img src="<?php echo plugin_dir_url(__FILE__) . 'images/ReadyShopLogo_WebSlogan_01.png'; ?>" style="height: 84px; width: 500px;">
            </div>
        </div>
        <div class="row col-md-12">
            <div class="col-md-3"><!-- MakeShift Offset --></div>
            <div class="col-md-3 collapse show readyshop-step-wizard-step">
                <div class="card" style="width: 18rem; cursor: pointer;" id="step-1" data-toggle="collapse" data-target=".readyshop-step-wizard-step">
                    <img class="card-img-top" src="<?php echo plugin_dir_url(__FILE__) . 'images/user-icon.png'; ?>" style="height: 222px; width: 222px;" alt="Card image cap">
                    <div class="card-body">
                        <h5 class="card-title">Step 1.</h5>
                        <p class="card-text">Click here to create your Ready Shop account or to sign in.</p>
                        <?php if(ReadyShop_PackageManager::checkRegistrationStep(0,'completed')): ?>
                            <a href="#" id="step-1-completed" style="color: green;" class="card-link">Completed <i class="fa fa-check-circle"></i> </a>
                        <?php else: ?>
                            <a href="#" id="step-1-getstarted" class="card-link">Get Started</a>
                            <a href="#" id="step-1-completed" style="color: green; display:none;" class="card-link">Completed <i class="fa fa-check-circle"></i> </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-3 offset-md-1 <?php if(ReadyShop_PackageManager::checkRegistrationStep(0,'completed')): echo 'collapse show readyshop-step-wizard-step'; endif; ?> " style="margin-left: 50px;">
                <div class="card" style="width: 18rem; height: 422px; cursor: pointer;" id="<?php if(ReadyShop_PackageManager::checkRegistrationStep(0,'completed')): echo 'step-2'; else: echo 'step-2-incomplete'; endif; ?>" data-toggle="collapse" <?php if(ReadyShop_PackageManager::checkRegistrationStep(0,'completed')): echo 'data-target=".readyshop-step-wizard-step"'; endif; ?>>
                    <img class="card-img-top" src="<?php echo plugin_dir_url(__FILE__) . 'images/cargo-512.png'; ?>" alt="Card image cap" style="height: 222px; width: 222px;">
                    <div class="card-body">
                        <h5 class="card-title">Step 2.</h5>
                        <p class="card-text">Click Here To Pick Your Products.</p>
                        <?php if(ReadyShop_PackageManager::checkRegistrationStep(1,'completed')): ?>
                            <a href="#" id="step-2-completed" style="color: green;" class="card-link">Completed <i class="fa fa-check-circle"></i> </a>
                        <?php else: ?>
                            <a href="#" id="step-2-getstarted" class="card-link">Get Started</a>
                            <a href="#" id="step-2-completed" style="color: green; display:none;" class="card-link">Completed <i class="fa fa-check-circle"></i> </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
<!--    --><?php //if(ReadyShop_PackageManager::checkRegistrationStep(0,'completed') && ReadyShop_PackageManager::checkRegistrationStep(1,'completed')): ?>
        <style type="text/css">
            .fa-dash-gradient {
                /*background: -webkit-gradient(linear, left top, left bottom, from(#873aff), to(#9AC94F));*/
                /*background: -linear-gradient(to bottom, #873aff 0%,#873aff 50%, #9AC94F 50%, #9AC94F 0%);*/
                /*background: linear-gradient(to bottom, #873aff 0% ,#873aff 50% , #9AC94F 50% ,#9AC94F 100%);*/
                /*background: linear-gradient(to bottom, #40317E 0% ,#40317E 50% , #9AC94F 50% ,#9AC94F 100%);*/
                background: linear-gradient(to bottom, #9AC94F 0% ,#9AC94F 50%, #40317E 50% ,#40317E 100%  );
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            .fa-store-gradient {
                /*background: -webkit-gradient(linear, left top, left bottom, from(#873aff), to(#9AC94F));*/
                /*background: -linear-gradient(to bottom, #873aff 0%,#873aff 50%, #9AC94F 50%, #9AC94F 0%);*/
                /*background: linear-gradient(to bottom, #873aff 0% ,#873aff 50% , #9AC94F 50% ,#9AC94F 100%);*/
                /*background: linear-gradient(to bottom, #40317E 0% ,#40317E 50% , #9AC94F 50% ,#9AC94F 100%);*/
                background: linear-gradient(to bottom, #9AC94F 0% ,#9AC94F 44%, #40317E 44% ,#40317E 100%  );
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            .well {
                min-height: 20px;
                padding: 19px;
                margin-bottom: 20px;
                background-color: #f5f5f5;
                border: 1px solid #e3e3e3;
                border-radius: 4px;
                -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.05);
                box-shadow: inset 0 1px 1px rgba(0,0,0,.05);
                margin-top: 5px;
            }
        </style>


        <!--div class="row col-md-12" id="finish-setup" style="display:none;<?php //if(ReadyShop_PackageManager::checkRegistrationStep(0,'completed') && ReadyShop_PackageManager::checkRegistrationStep(1,'completed') ): echo ''; else: echo 'display:none;'; endif; ?>/*margin-top: 10px; padding-top: 40px; padding-bottom: 40px; background-color: #f0ffe5; border-top: 1px solid #d5ffb7; border-bottom: 1px solid #d5ffb7; */"-->
        <div class="row col-md-12 finish-setup-row" id="finish-setup" style="<?php if(ReadyShop_PackageManager::checkRegistrationStep(0,'completed') && ReadyShop_PackageManager::checkRegistrationStep(1,'completed') ): echo ''; else: echo 'display:none;'; endif; ?>">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <center>
                    <img src="<?php echo plugin_dir_url(__FILE__) . 'images/ReadyShopLogo_WebSlogan_02.png'; ?>" style="margin-top: 50px">
    <!--                <br>-->
    <!--                <br>-->
    <!--                <button type="submit" class="btn btn-success btn-lg"  id="finish-registration" style="cursor: pointer;">Finish</button>-->
                </center>
            </div>
        </div>


        <div class="row col-md-12 finish-setup-row" style="margin-top: 50px; <?php if(ReadyShop_PackageManager::checkRegistrationStep(0,'completed') && ReadyShop_PackageManager::checkRegistrationStep(1,'completed') ): echo ''; else: echo 'display:none;'; endif; ?>">
            <div class="col-md-6" style="text-align: center">
<!--                <i class="fas fa-tachometer-alt" style="font-size: 12em;background: -webkit-gradient(linear, left, top, bottom, from(#873aff), to(#21b12c)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>-->
                <i class="fas fa-tachometer-alt fa-dash-gradient" style="font-size: 12em;"></i>
                <br>
                <br>
                <button style="cursor: pointer;" data-option="dashboard" type="button" class="btn btn-success finish-registration" data-toggle="tooltip" data-placement="right" title="Go here to view your sales & commissions">View My Dashboard</button>
<!--                <br>-->
<!--                <div class="well" style="margin-top: 20px; text-align: left; width: 50%; margin-left: 25%">-->
<!--                    <ul>-->
<!--                        <li>Go Here To View Your Sales</li>-->
<!--                        <li>Go Here To View Your Comissions</li>-->
<!--                    </ul>-->
<!--                </div>-->
            </div>
            <div class="col-md-6" style="text-align: center">
                <i class="fas fa-store-alt fa-store-gradient" style="font-size: 12em;"></i>
                <br><br>
                <button style="cursor: pointer" data-option="store" type="button" class="btn btn-success finish-registration" data-toggle="tooltip" data-placement="right" title="Go here to see how your shop looks">View My Store</button>
<!--                <br>-->
<!--                <div class="well" style="margin-top: 20px; text-align: left; width: 50%; margin-left: 25%">-->
<!--                    <ul>-->
<!--                        <li>Go here to see how your shop looks</li>-->
<!--                        <li>This is where you can send your end customers to shop</li>-->
<!--                    </ul>-->
<!--                </div>-->
            </div>
        </div>
<!--        --><?php //endif; ?>
<!--    <div class="row col-md-12" id="finish-setup" style="--><?php ////if(ReadyShop_PackageManager::checkRegistrationStep(0,'completed') && ReadyShop_PackageManager::checkRegistrationStep(1,'completed') ): echo ''; else: echo 'display:none;'; endif; ?><!--/*/*margin-top: 10px; padding-top: 40px; padding-bottom: 40px; background-color: #f0ffe5; border-top: 1px solid #d5ffb7; border-bottom: 1px solid #d5ffb7;*/*/">-->
<!--       <div class="col-md-4"></div>-->
<!--       <div class="col-md-4">-->
<!--           <center>-->
<!--               <img src="--><?php ////echo plugin_dir_url(__FILE__) . 'images/ReadyShopLogo_WebSlogan_02.png'; ?><!--">-->
<!--                <br>-->
<!--                <br>-->
<!--                <button type="submit" class="btn btn-success btn-lg"  id="finish-registration" style="cursor: pointer;">Finish</button>-->
<!--            </center>-->
<!--        </div>-->
<!--    </div>-->

    <div class="row col-md-12 collapse" id="step-1-content" style="margin-top: 30px;">
        <?php readyshop_affiliate_account(true); ?>
    </div>

    <div class="row col-md-12 collapse" id="step-2-content" style="margin-top: 30px;">
    <?php
        if(ReadyShop_PackageManager::checkRegistrationStep(0,'completed') && !ReadyShop_PackageManager::checkRegistrationStep(1,'completed')):
            readyshop_affiliate_products(true);
        endif;
    ?>
    </div>

    <?php if(ReadyShop_PackageManager::checkRegistrationStep(0,'completed') && !ReadyShop_PackageManager::checkRegistrationStep(1,'completed')): ?>
        <script type="text/javascript">
            localStorage.setItem('rs_registration_step_1','completed');
        </script>

    <?php else: ?>
        <script type="text/javascript">
            localStorage.setItem('rs_registration_step_1','incomplete');
        </script>

    <?php endif; ?>

    <?php
    readyshop_include_twitter_bootstrap();
    readyshop_include_setup_management();
    readyshop_help_desk();
}

function readyshop_help_desk() {
    $html = "
    
    <!-- Start of readyshop Zendesk Widget script -->
    <script>/*<![CDATA[*/window.zE||(function(e,t,s){var n=window.zE=window.zEmbed=function(){n._.push(arguments)}, a=n.s=e.createElement(t),r=e.getElementsByTagName(t)[0];n.set=function(e){ n.set._.push(e)},n._=[],n.set._=[],a.async=true,a.setAttribute(\"charset\",\"utf-8\"), a.src=\"https://static.zdassets.com/ekr/asset_composer.js?key=\"+s, n.t=+new Date,a.type=\"text/javascript\",r.parentNode.insertBefore(a,r)})(document,\"script\",\"8c83c8be-1801-4279-af3a-45c1ee0bed0e\");/*]]>*/</script>
    <!-- End of readyshop Zendesk Widget script -->

    ";

    echo $html;
    return;
}

function readyshop_help() {
    readyshop_include_twitter_bootstrap();
    ?>
    <center>
        <img src="<?php echo plugin_dir_url(__FILE__) . 'images/ReadyShopLogo_WebSlogan_01.png'; ?>"><br>
        <a target="_blank" href="https://readyshop.zendesk.com/hc/en-us"><img src="<?php echo plugin_dir_url(__FILE__) . 'images/virtual-help-desk.png'; ?>"/></a>

        <p>For Further F.A.Q's & Support Please Visit our <a target="_blank" href="https://readyshop.zendesk.com/hc/en-us">Help Center Page</a>.</p>
        <p>You can also submit a help request below by clicking the [Help] button on the bottom right of your screen.</p>

    </center>

    <center>
    <!-- Base F.A.Q Area -->
    <div class="panel-group">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" href="#collapse1">Need Help Adding [Shop] Page To Menu? Or The [Shop] Page says Scheduled?</a>
                </h4>
            </div>
            <div id="collapse1" class="panel-collapse collapse">
                <ul class="list-group">
                    <li class="list-group-item">
                        <img src="<?php echo plugin_dir_url(__FILE__) . 'images/help/shop-page-menu/1.PNG'; ?>" style="max-width: 1500px;">
                        <h3 style="margin-top: 20px;">Step 1: Go To [Pages > All Pages] If You See The [Shop] Page as Scheduled. Click To Edit The Proceed To <b>Step 2</b></h3>
                    </li>
                    <li class="list-group-item">
                        <img src="<?php echo plugin_dir_url(__FILE__) . 'images/help/shop-page-menu/2.PNG'; ?>" style="max-width: 1500px;">
                        <h3 style="margin-top: 20px;">Step 2: You Should See The "Scheduled For: [DateTime]" In The Top Right Under [Publish Dropdown]</h3>
                    </li>
                    <li class="list-group-item">
                        <img src="<?php echo plugin_dir_url(__FILE__) . 'images/help/shop-page-menu/3.PNG'; ?>" style="max-width: 1500px;">
                        <h3 style="margin-top: 20px;">Step 3:  Click To Edit Then Proceed To <b>Step 4</b></h3>
                    </li>
                    <li class="list-group-item">
                        <img src="<?php echo plugin_dir_url(__FILE__) . 'images/help/shop-page-menu/4.PNG'; ?>" style="max-width: 1500px;">
                        <h3 style="margin-top: 20px;">Step 4:  Edit The Time To Be In The Past. Then Click [OK] Button Then [Update] Button. Then Proceed To <b>Step 5</b></h3>
                    </li>
                    <li class="list-group-item">
                        <img src="<?php echo plugin_dir_url(__FILE__) . 'images/help/shop-page-menu/5.PNG'; ?>" style="max-width: 1500px;">
                        <h3 style="margin-top: 20px;">Step 5:  Click [Publish] Button Then Proceed To <b>Step 6</b></h3>
                    </li>
                    <li class="list-group-item">
                        <img src="<?php echo plugin_dir_url(__FILE__) . 'images/help/shop-page-menu/6.PNG'; ?>" style="max-width: 1500px;">
                        <h3 style="margin-top: 20px;">Step 6:  The Page Should Now Say Published. </h3>
                    </li>
                    <li class="list-group-item">
                        <img src="<?php echo plugin_dir_url(__FILE__) . 'images/help/shop-page-menu/7.PNG'; ?>" style="max-width: 1500px;">
                        <h3 style="margin-top: 20px;">Step 7:  Go To [Appearance > Menus] Page</h3>
                    </li>
                    <li class="list-group-item">
                        <img src="<?php echo plugin_dir_url(__FILE__) . 'images/help/shop-page-menu/8.PNG'; ?>" style="max-width: 1500px;">
                        <h3 style="margin-top: 20px;">Step 8:  Check Shop Page Checkbox Then Click [Add To Menu] Button</h3>
                    </li>
                    <li class="list-group-item">
                        <img src="<?php echo plugin_dir_url(__FILE__) . 'images/help/shop-page-menu/9.PNG'; ?>" style="max-width: 1500px;">
                        <h3 style="margin-top: 20px;">Step 9:  Click [Save Menu] Button</h3>
                    </li>
                    <li class="list-group-item">
                        <img src="<?php echo plugin_dir_url(__FILE__) . 'images/help/shop-page-menu/10.PNG'; ?>" style="max-width: 1500px;">
                        <h3 style="margin-top: 20px;">Step 10: The Page Should Say [Top/Nav Bar Has Been Updated]</h3>
                    </li>
                    <li class="list-group-item">
                        <img src="<?php echo plugin_dir_url(__FILE__) . 'images/help/shop-page-menu/11.PNG'; ?>" style="max-width: 1500px;">
                        <h3 style="margin-top: 20px;">Final Step:  Go To Your [Shop Page] By Clicking The [Shop] Navigation Button!</h3>
                    </li>
                </ul>
                <div class="panel-footer">You Should Be All Set For People To View Your Store!</div>
            </div>
        </div>
    </div>
    </center>
    <?php
    readyshop_help_desk();
}

/**
 * Register a custom menu page.
 * #images
//        plugins_url( 'drop-in-commerce-wp/images/icon.png' ),
//        'https://www.readyshop.com/wp-content/uploads/2016/08/cropped-favicon-32x32.jpg',
 */
function wpdocs_register_my_custom_menu_page() {

    /**
     * Display Different Panes
     */
    if(!ReadyShop::REGISTRATION_PROCESS_ACTIVE || (ReadyShop_UserService::isLoggedIn() && ReadyShop_PackageManager::checkRegistrationCompletion())) {
        add_menu_page(
            __( 'RS | ReadyShop', 'textdomain' ),
            'ReadyShop',
            'manage_options',
            'readyshop', #drop-in-commerce-wp/drop-in-commerce.php
            'readyshop_affiliate_dash',
            'dashicons-cart',
            6
        );
        add_submenu_page( 'readyshop', 'RS | Dashboard', 'Dashboard',
            'manage_options', 'readyshop.home','readyshop_affiliate_dash');
        add_submenu_page( 'readyshop', 'RS | Products', 'Manage Products',
            'manage_options', 'readyshop.products','readyshop_affiliate_products');
        add_submenu_page( 'readyshop', 'RS | Account', 'Account',
            'manage_options', 'readyshop.account','readyshop_affiliate_account');
        add_submenu_page( 'readyshop', 'RS | Help', 'Help',
            'manage_options', 'readyshop.help','readyshop_help');

    } else {
        add_menu_page(
            __( 'RS | ReadyShop', 'textdomain' ),
            'ReadyShop',
            'manage_options',
            'readyshop', #drop-in-commerce-wp/drop-in-commerce.php
            'readyshop_affiliate_setup',
            'dashicons-cart',
            6
        );


        add_submenu_page( 'readyshop', 'RS | Setup', 'Login | Register',
            'manage_options', 'readyshop.setup','readyshop_affiliate_setup');

    }

    remove_submenu_page( 'readyshop', 'readyshop.main' );


}
add_action( 'admin_menu', 'wpdocs_register_my_custom_menu_page' );


/*Remove redundant 'ReadyShop' submenu, this is the same page as 'Home' */
function readyshop_nstrm_remove_admin_submenus() {
    global $submenu;

    end($submenu);
    $key = key($submenu);
    reset($submenu);
    unset($submenu['readyshop'][0]);

}
add_action( 'admin_menu', 'readyshop_nstrm_remove_admin_submenus', 999 );




/**
 * Register AJAX Services
 */

add_action( 'wp_ajax_readyshop_product_activation_management', 'readyshop_product_activation_management' );
add_action( 'wp_ajax_nopriv_readyshop_product_activation_management', 'readyshop_product_activation_management' );

function readyshop_product_activation_management() {
    #global $wpdb; // this is how you get access to the database

    $productId = $_POST['productId'];
    $option = $_POST['option'];


    //check if product was added to store if so [complete: registration step-2]
    //@todo should perform this operation after successful callback check of ReadyShop_ProductService::manageProduct();
    if($option === 'add') {
        ReadyShop_PackageManager::updateRegistrationStep(1,true);
    }

    //update the product
    $response = ReadyShop_ProductService::manageProduct($productId, $option, false);

    //return response to client
    echo $response;

    wp_die(); // this is required to terminate immediately and return a proper response
}


add_action( 'wp_ajax_readyshop_batch_product_activation_management', 'readyshop_batch_product_activation_management' );
add_action( 'wp_ajax_nopriv_readyshop_batch_product_activation_management', 'readyshop_batch_product_activation_management' );

function readyshop_batch_product_activation_management() {
    #global $wpdb; // this is how you get access to the database

    $productArray = $_POST['productArray'];
    $option = $_POST['option'];


    //check if product was added to store if so [complete: registration step-2]
    //@todo should perform this operation after successful callback check of ReadyShop_ProductService::manageProduct();
    if($option === 'add') {
        ReadyShop_PackageManager::updateRegistrationStep(1,true);
    }

//    var_dump($productArray); $response = [];

    //update the product
    $response = ReadyShop_ProductService::batchManageProducts($productArray, $option, false);

    //return response to client
    echo $response;

    wp_die(); // this is required to terminate immediately and return a proper response
}


add_action( 'wp_ajax_readyshop_profile_information_management', 'readyshop_profile_information_management' );
add_action( 'wp_ajax_nopriv_readyshop_profile_information_management', 'readyshop_profile_information_management' );
/**
 * Settings Management Action
 *
 *
 * @todo Will create a new user if none exists attached to the generated appToken for this installation.
 * @todo Will Bind The 2 together.
 * @todo Will Throw warning if a user account already exists attached to the domain.
 * @todo Will recommend password reset.
 * @todo If User Logs In Will Fetch Same Information & Re-Populate The Settings Form.
 *
 * Information collected will be used for payout service & customer support.
 */
function readyshop_profile_information_management() {
    $fields = array(
        'firstName',
        'lastName',
        'email',
        'inlineCheckbox1', //receive updates via email
        'password1',
        'password2',
        'address1',
        'address2',
        'city',
        'state',
        'zipcode',
        'paypalEmail',
        'fullName',
        'businessName',
        'inputTaxClassification',
        'businessAddress1',
        'businessAddress2',
        'businessCity',
        'businessState',
        'businessZipcode',
        'socialSecurity',
        'employerIdentificationNumber',
        'w9FormCertification'

    );
    $data = array();
    foreach($_POST as $key => $postData) {
        if(in_array($key, $fields)) {
            $data[$key] = $postData;
        }
    }


    //perform $_POST Action to [^/wp/settings] Endpoint
    $response = ReadyShop_SettingsService::setSettings($data);

    if($response['status'] == 200) {
        ReadyShop_UserService::setStatelessToken($response['token']);
        ReadyShop_PackageManager::updateRegistrationStep(0,true);
    }

    //echo JSON_RESPONSE for client to update UI
    echo json_encode($response);
    wp_die();
}


add_action( 'wp_ajax_readyshop_settings_management_auth_form', 'readyshop_settings_management_auth_form' );
add_action( 'wp_ajax_readyshop_settings_management_auth_form', 'readyshop_settings_management_auth_form' );

/**
 * This function is responsible for the authentication of a user on the [ReadyShop > Settings] Page
 * -- To be used to pull information on previous installation attempt & to keep user logged in once registered.
 */
function readyshop_settings_management_auth_form() {

    //gather client side arguments
    $args = array(
        'userName' => $_POST['loginEmail'],
        'passWord' => $_POST['loginPassword'],
        'appToken' => ReadyShop_PackageManager::getApplicationToken()
    );

    //send authorization request
    $response = ReadyShop_UserService::loginUser($args);

//    ReadyShop::dump(array($args, $response));

    //return server response to client
    if($response !== false) {
        //@todo need to perform this update another way - by pulling the information from the user object from the API Server
        //auto update all registration steps to complete
        ReadyShop_PackageManager::updateRegistrationStep(0,true);
        ReadyShop_PackageManager::updateRegistrationStep(1,true);
        ReadyShop_PackageManager::updateRegistrationStep(2,true);

        echo json_encode($response);
    } else {
        echo json_encode(array('status'=> 404, 'message' => 'The user account could not be found. Please try again!','data'=>[$args]));
    }

    //exit to prevent further output to client
    exit;
}

add_action( 'wp_ajax_readyshop_profile_registration_completion_action', 'readyshop_profile_registration_completion_action' );
add_action( 'wp_ajax_readyshop_profile_registration_completion_action', 'readyshop_profile_registration_completion_action' );

/**
 * Will return a success response on submitting the button
 * @todo need to perform internal check to make sure the value was successfully updated
 */
function readyshop_profile_registration_completion_action() {
    ReadyShop_PackageManager::updateRegistrationStep(2, true);
    if(ReadyShop_PackageManager::checkRegistrationCompletion()) {
        echo json_encode(array('status' => 200, 'message' => 'Registration Was Successfully Completed'));
    } else {
        echo json_encode(array('status' => 500, 'message' => 'Registration Could Not Be Completed!'));
    }
    exit;
}


add_action( 'wp_ajax_readyshop_settings_management_logout_action', 'readyshop_settings_management_logout_action' );
add_action( 'wp_ajax_readyshop_settings_management_logout_action', 'readyshop_settings_management_logout_action' );

/**
 * This function is responsible for removing the stateless-token
 */
function readyshop_settings_management_logout_action() {
    $response = ReadyShop_UserService::logoutUser();
    ReadyShop_PackageManager::updateRegistrationStep(0,false);
    ReadyShop_PackageManager::updateRegistrationStep(1,false);
    ReadyShop_PackageManager::updateRegistrationStep(2,false);

    if($response) {
        echo json_encode(['status'=>200,'message'=>'User was successfully logged out!']);
    } else {
        echo json_encode(['status'=>500,'message'=>'User could not be logged out!']); //logging out a user should never actually be a problem <(-_0)>
    }

    exit;
}


// Filter wp_nav_menu() to add additional links and other output
//function rs_new_nav_menu_items($items) {
//    $homelink = '<li class="home"><a href="' . home_url( '/shop' ) . '">' . __('Shop') . '</a></li>';
//    // add the home link to the end of the menu
//    $items = $items . $homelink;
//    return $items;
//}
//add_filter( 'wp_nav_menu_items', 'rs_new_nav_menu_items' );


/**
 * Create Webhook For Heartbeat And Plugin Version Information
 */
/**
 * This is our callback function that embeds our phrase in a WP_REST_Response
 */
function readyshop_api_heartbeat_response() {
    global $wp_version;
    // rest_ensure_response() wraps the data we want to return into a WP_REST_Response, and ensures it will be properly returned.
    return rest_ensure_response( ReadyShop_PackageManager::heartbeat() );
}

/**
 * This is our callback for getting the systems logging information
 */
function readyshop_api_logging_response() {
    return rest_ensure_response(ReadyShop_PackageManager::getErrorLogInformation());
}

/**
 * This function is where we register our routes for our heartbeat endpoint.
 */
function readyshop_api_register() {
    // register_rest_route() handles more arguments but we are going to stick to the basics for now.
    register_rest_route( 'ready-shop/v1', '/heartbeat', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::READABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'readyshop_api_heartbeat_response',
    ) );

    //this route is used to return debugging and logging information about the server
    register_rest_route('ready-shop/v1', 'logging',array(
       'methods' => WP_REST_Server::READABLE,
       'callback' => 'readyshop_api_logging_response'
    ));
}

add_action( 'rest_api_init', 'readyshop_api_register' );