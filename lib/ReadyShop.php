<?php
/**
 * Created by PhpStorm.
 * Author: Daniel P. Baddeley JR <dan@bluewatermedia.tv>
 * Date: 5/20/16
 * Time: 1:44 PM
 *
 * -- Backwards Compatible with PHP 5.3.28
 * - Did not use short array syntax
 *
 * ---- used practices as such
 *   $atts = $this->getAttributes();
 *   $type = $atts['type'];
 *   if(in_array($type, $this->getTypeDefinitions())) {
 * ---- instead of
 *   if(in_array($this->getAttributes()['type'],$this->getTypeDefinitions())) {
 *
 * *** This is due to older versions of PHP not having the ability to directly manipulate the return response object|array
 */
namespace ReadyShop\lib;

require_once 'ReadyShop_APIService.php';

use ReadyShop\lib\ReadyShop_APIService as ReadyShop_APIService;

/**
 * Class ReadyShop
 * @package library
 */
class ReadyShop {

    const PLUGIN_VERSION = '1.0.17';

    const REGISTRATION_PROCESS_ACTIVE = false;

    /**
     * Class Constants
     *
     */
    //insecured domain
    const HOST_DOMAIN_INSECURE = 'http://api.readyshop.com';
    //secured domain
    const HOST_DOMAIN_SECURED = 'https://api.readyshop.com';

    /**
     * @var $scriptContent string
     */
    private $scriptContent;

    /**
     * @var $embedContent
     */
    private $embedContent;
    /**
     * @var $iframeContent
     */
    private $iframeContent;

    /**
     * @var string
     */
    private $iframeAssets;

    /**
     * @var string
     */
    private $iframeScript;

    /**
     * @var $typeDefinitions
     */
    private $typeDefinitions;

    /**
     * @var $attributes
     */
    private $attributes;

    /**
     * @var string $token
     * @see ReadyShop_PackageManager::getApplicationToken()
     */
    private $token;

    /**
     * DropInCommerce constructor.
     */
    public function __construct($attributes, $token)
    {
        $this->setAttributes($attributes);
        $this->setToken($token);
        $this->setScriptContent();
        $this->setEmbedContent();
        $this->setTypeDefinitions();
        $this->setIframeAssets();
        $this->setIframeScript();
        $this->setIframeContent($this->getAttributes());
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }


    /**
     * @return string
     */
    public function getScriptContent()
    {
        return $this->scriptContent;
    }

    /**
     * @param string $scriptContent
     * @return string
     */
    public function setScriptContent($scriptContent = null)
    {

        if($scriptContent !== null) {
            $this->scriptContent = $scriptContent;

        } else {
//            return '<script type="text/javascript" src="'.self::HOST_DOMAIN_SECURED.'/v1.0/open/include" app-id="' . self::getToken() . '" async></script><div id="drop-in-commerce"></div>';
            //$html = file_get_contents('https://api.readyshop.com/open/jquery?appId=' . self::getToken() . '&sort=undefined#');

            $html = ReadyShop_APIService::get('/open/jquery?appId='.self::getToken().'&sort=undefined', self::HOST_DOMAIN_SECURED, false);
            return $html;
        }
    }



    /**
     * @return mixed
     */
    public function getEmbedContent()
    {
        return $this->embedContent;
    }

    /**
     * @return mixed
     */
    public function setEmbedContent()
    {
//        $content = file_get_contents(self::HOST_DOMAIN_SECURED.'/open?sort='.$_GET['sort']);
//        $find = self::HOST_DOMAIN_INSECURE;
//        $replace = self::HOST_DOMAIN_SECURED;
//        return $this->embedContent = str_replace($find, $replace, $content);

        if(isset($_GET['sort']) && $_GET['sort'] !== '') {
            $sort = $_GET['sort'];
        } else {
            $sort = 'undefined';
        }

        //$html = file_get_contents('https://api.readyshop.com/open/jquery?appId=' . self::getToken() . '&sort=undefined#');
        $html = ReadyShop_APIService::get('/open/jquery?appId='.self::getToken().'&sort=' . $sort, self::HOST_DOMAIN_SECURED, false);
        $this->embedContent = $html;

    }

    /**
     * @return string
     */
    private function setIframeAssets() {
        $html = '';

        $assets = array(
//            '//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js',
            '//api.readyshop.com/assets/js/FrontFacing/iframeResizer.js'
        );
        foreach($assets as $key => $val) {
            $html .= '<script type="text/javascript" src="'.$val.'"></script>';
        }
        return $this->iframeAssets = $html;
    }

    /**
     * @return string
     */
    public function getIframeAssets(){
        if(isset($this->iframeAssets) && $this->iframeAssets != '') {
            return $this->iframeAssets;
        } else {
            return $this->setIframeAssets();
        }
    }

    /**
     * @return string
     */
    public function setIframeScript() {
        return $this->iframeScript = '
            <script type="text/javascript" language="javascript">
//                var iframes = iFrameResize({log:true},"#dic_iframe");
            iFrameResize({log:true});
            </script>
        ';
    }

    /**
     * @return string
     */
    public function getIframeScript() {
        return $this->iframeScript;
    }

    /**
     * @return mixed
     */
    public function getIframeContent() {
        return '<script type="text/javascript" src="'.self::HOST_DOMAIN_SECURED.'/v1.0/open/include" app-id="' . self::getToken() . '" async></script><div id="drop-in-commerce"></div>';
    }

    /**
     * @param $a
     * The purpose of this plugin is to allow the direct integration of these pages  for other people to see
     * This will sandbox the embedded information as to not conflict with any existing styles the application by currently be utilizing
     * ---
     * This takes optional paremeters to allow the resizing of the embedded content without too much effort from the end user
     * the defaults for this application are 100% height and width
     * -- height = '100%' || '100px - 1000px' etc.
     * -- width = '100%' || '100px - 1000px' etc.
     *
     */
    public function setIframeContent($a) {
        $this->iframeContent = $this->getIframeAssets();
        $this->iframeContent .= '<iframe id="dic_iframe" src="'.self::HOST_DOMAIN_SECURED.'/open/jquery" style="width:'.$a['width'].';" width="'.$a['width'].'"';
        $this->iframeContent .= ($a['force_full_size'] == 'true') ? ' frameborder="0" scrolling="no"' : '';
        $this->iframeContent .= '></iframe>';
        $this->iframeContent .= $this->getIframeScript();
    }


    /**
     * @return mixed
     */
    public function getTypeDefinitions()
    {
        return $this->typeDefinitions;
    }

    /**
     * @param mixed $typeDefinitions
     */
    private function setTypeDefinitions($typeDefinitions = null)
    {

        if(isset($typeDefinitions) && $typeDefinitions !== null) {
            $this->typeDefinitions = $typeDefinitions;
        } else {
            $this->typeDefinitions =  array(
                'iframe',
                'embed'
            );
        }
    }

    /**
     * @return mixed
     */
    private function getAttributes() {
        return $this->attributes;
    }

    /**
     * @param $attributes
     */
    private function setAttributes($attributes) {
        $this->attributes = $attributes;
    }

    /**
     * ***** MAIN CONTENT PROCESSOR *****
     *
     * @return mixed|string
     *
     * --- backwards compatible with 5.3.28
     * --- 5.5.6 would allow : if(in_array($this->getAttributes()['type'],$this->getTypeDefinitions())) {
     */
    public function execute() {
//        $atts = $this->getAttributes();
//        $type = $atts['type'];
//        if(in_array($type, $this->getTypeDefinitions())) {
//            switch ($type) {
//                case 'script':
//                    return self::getScriptContent();
//                    break;
//                case 'iframe':
//                    return self::getIframeContent();
//                    break;
//                case 'embed':
//                    return self::getEmbedContent();
//                    break;
//            }
//            //return self::getIframeContent();
//        } else {
//            return self::getIframeContent();
//        }

        return self::getEmbedContent();
//        return self::getScriptContent();
    }

    /**
     * Pretty Print Passed Variables To Client
     * @param mixed ...$args
     *
     * @todo Removed Variadic Function Arguments For Backwards Compatibility *BEFORE* PHP-v5.6
     */
    public static function dump($args) {
        echo '<pre>';
        foreach($args as $key => $val) {
            print_r($val);
        }
        echo '</pre>';
    }

    //will return the 50 states & US territories
    public static function states() {

        return array(
            'AL'=>'ALABAMA',
            'AK'=>'ALASKA',
            'AS'=>'AMERICAN SAMOA',
            'AZ'=>'ARIZONA',
            'AR'=>'ARKANSAS',
            'CA'=>'CALIFORNIA',
            'CO'=>'COLORADO',
            'CT'=>'CONNECTICUT',
            'DE'=>'DELAWARE',
            'DC'=>'DISTRICT OF COLUMBIA',
            'FM'=>'FEDERATED STATES OF MICRONESIA',
            'FL'=>'FLORIDA',
            'GA'=>'GEORGIA',
            'GU'=>'GUAM GU',
            'HI'=>'HAWAII',
            'ID'=>'IDAHO',
            'IL'=>'ILLINOIS',
            'IN'=>'INDIANA',
            'IA'=>'IOWA',
            'KS'=>'KANSAS',
            'KY'=>'KENTUCKY',
            'LA'=>'LOUISIANA',
            'ME'=>'MAINE',
            'MH'=>'MARSHALL ISLANDS',
            'MD'=>'MARYLAND',
            'MA'=>'MASSACHUSETTS',
            'MI'=>'MICHIGAN',
            'MN'=>'MINNESOTA',
            'MS'=>'MISSISSIPPI',
            'MO'=>'MISSOURI',
            'MT'=>'MONTANA',
            'NE'=>'NEBRASKA',
            'NV'=>'NEVADA',
            'NH'=>'NEW HAMPSHIRE',
            'NJ'=>'NEW JERSEY',
            'NM'=>'NEW MEXICO',
            'NY'=>'NEW YORK',
            'NC'=>'NORTH CAROLINA',
            'ND'=>'NORTH DAKOTA',
            'MP'=>'NORTHERN MARIANA ISLANDS',
            'OH'=>'OHIO',
            'OK'=>'OKLAHOMA',
            'OR'=>'OREGON',
            'PW'=>'PALAU',
            'PA'=>'PENNSYLVANIA',
            'PR'=>'PUERTO RICO',
            'RI'=>'RHODE ISLAND',
            'SC'=>'SOUTH CAROLINA',
            'SD'=>'SOUTH DAKOTA',
            'TN'=>'TENNESSEE',
            'TX'=>'TEXAS',
            'UT'=>'UTAH',
            'VT'=>'VERMONT',
            'VI'=>'VIRGIN ISLANDS',
            'VA'=>'VIRGINIA',
            'WA'=>'WASHINGTON',
            'WV'=>'WEST VIRGINIA',
            'WI'=>'WISCONSIN',
            'WY'=>'WYOMING',
            'AE'=>'ARMED FORCES AFRICA \ CANADA \ EUROPE \ MIDDLE EAST',
            'AA'=>'ARMED FORCES AMERICA (EXCEPT CANADA)',
            'AP'=>'ARMED FORCES PACIFIC'
        );
    }

    //returns the federal tax classifications for the user settings management [W9-Form]
    public static function federalTaxClassifications() {
        return array(
            'Individual/Sole Proprietor',
            'C Corporation',
            'S Corporation',
            'Partnership',
            'Trust/Estate',
            'Limited Liability Company',
            'Exempt Payee',
            'Other/NA'
        );
    }
}
