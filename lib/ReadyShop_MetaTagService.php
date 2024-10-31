<?php
/**
 * @author Daniel P. Baddeley JR <dan@bluewatermedia.tv>
 * @date: 11/22/17 9:10 PM
 * @info This service is used to fetch Meta-Tag information for the different social sharing services for products
 * hosted on the domain owners store
 */

namespace ReadyShop\lib;

require_once 'ReadyShop.php';
require_once 'ReadyShop_APIService.php';
require_once 'ReadyShop_PackageManager.php';

use ReadyShop\lib\ReadyShop as ReadyShop;
use ReadyShop\lib\ReadyShop_APIService as ReadyShop_APIService;
use ReadyShop\lib\ReadyShop_PackageManager as ReadyShop_PackageManager;

/**
 * Class ReadyShop_MetaTagService
 * @package lib
 */
class ReadyShop_MetaTagService
{

    /**
     * @param $productId
     * @param array $filters [ brand => '*', category => '*', color => '*' ]
     * @return array|mixed|object
     */
    public static function getFacebookMetaData($productId, array $filters = null, $hostname) {
        $query = '?';
        foreach($filters as $key => $filter) {
            $query .= $key . '=' . $filter . '&';
        }
        $query = substr($query, 0, -1);
        $meta = ReadyShop_APIService::post('/meta/fb/'. ReadyShop_PackageManager::getApplicationToken().'/'.$productId . $query, array('hostname'=>$hostname),ReadyShop::HOST_DOMAIN_SECURED, false);
        return $meta;
    }

    /**
     * @param $productId
     * @param array $filters [ brand => '*', category => '*', color => '*' ]
     * @return array|mixed|object
     */
    public static function getTwitterMetaData($productId, array $filters = null, $hostname) {
        $query = '?';
        foreach($filters as $key => $filter) {
            $query .= $key . '=' . $filter . '&';
        }
        $query = substr($query, 0, -1);
        $meta = ReadyShop_APIService::post('/meta/tw/'. ReadyShop_PackageManager::getApplicationToken().'/'.$productId . $query, array('hostname'=>$hostname),ReadyShop::HOST_DOMAIN_SECURED, false);
        return $meta;
    }
}