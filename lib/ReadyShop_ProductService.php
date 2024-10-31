<?php
/**
 * @author Daniel P. Baddeley JR <dan@bluewatermedia.tv>
 * @date: 9/14/17 6:03 PM
 * @info This Will Fetch Product Information From The ReadyShop Service Layer
 */

namespace ReadyShop\lib;

/** Dependencies */
require_once 'ReadyShop.php';
require_once 'ReadyShop_APIService.php';
require_once 'ReadyShop_PackageManager.php';

use ReadyShop\lib\ReadyShop as ReadyShop;
use ReadyShop\lib\ReadyShop_APIService as ReadyShop_APIService;
use ReadyShop\lib\ReadyShop_PackageManager as ReadyShop_PackageManager;

/**
 * Class ReadyShop_ProductService
 * @package lib
 */
class ReadyShop_ProductService
{

    const PRODUCT_FILE_CACHE = __DIR__ . '/../var/product-list-cache';

    /**
     * Returns Full Product List
     * @param int $offset
     * @param int $limit
     * @return array|mixed|object
     * @link https://api.readyshop.com/v1.0/products/view/json
     * @todo Allow Isolation By Brand
     */
    public static function getProducts($offset = 0, $limit = 100) {

        if(strtotime(date('Y-m-d H:i:s') - filemtime(self::PRODUCT_FILE_CACHE)) > (60*60*24)) {
            $products = ReadyShop_APIService::get('/v1.0/products/view/json/'.$offset.'/'.$limit);
            file_put_contents(self::PRODUCT_FILE_CACHE, json_encode($products));
        } else if (file_get_contents(self::PRODUCT_FILE_CACHE) === '') {
            $products = ReadyShop_APIService::get('/v1.0/products/view/json/'.$offset.'/'.$limit);
            file_put_contents(self::PRODUCT_FILE_CACHE, json_encode($products));
        } else if (count(json_decode(file_get_contents(self::PRODUCT_FILE_CACHE),true)) >= 1) {
            $products = json_decode(file_get_contents(self::PRODUCT_FILE_CACHE),true);
        }

        return $products;
    }

    /**
     * @return array|mixed|object
     */
    public static function getAddedProducts() {
        return ReadyShop_APIService::get('/v1.0/installation/domain/product/listings/'.ReadyShop_PackageManager::getApplicationToken());
    }

    /**
     * @return array|mixed|object
     */
    public static function getCategories() {
        return ReadyShop_APIService::get('/v1.0/installation/domain/product/categories/'.ReadyShop_PackageManager::getApplicationToken());
    }

    /**
     * @return array|mixed|object
     */
    public static function getBrands() {
        return ReadyShop_APIService::get('/v1.0/installation/domain/product/brands/'.ReadyShop_PackageManager::getApplicationToken());
    }

    /**
     * @return array|mixed|object
     */
    public static function getCategoriesByBrand() {
        return ReadyShop_APIService::get('/v1.0/installation/domain/product/categoriesByBrand/'.ReadyShop_PackageManager::getApplicationToken());
    }

    /**
     * @return array|mixed|object
     */
    public static function getProductGroupings() {
        return ReadyShop_APIService::get('/v1.0/installation/domain/product/groupings/'.ReadyShop_PackageManager::getApplicationToken());
    }

    /**
     * @param $productId
     * @param $option
     * @param $jsonDecode
     * @return array|mixed|string
     */
    public static function manageProduct($productId, $option, $jsonDecode = false){
        return ReadyShop_APIService::post('/wp/products/activation/'.$productId.'/'.$option.'/'.ReadyShop_PackageManager::getApplicationToken(),array('appToken'=>ReadyShop_PackageManager::getApplicationToken()),ReadyShop::HOST_DOMAIN_SECURED,$jsonDecode);
    }

    /**
     * Batch Update Product Listings
     * @param $productArray
     * @param $option
     * @param $jsonDecode
     * @return array|mixed|string
     */
    public static function batchManageProducts($productArray, $option, $jsonDecode = false){
        return ReadyShop_APIService::post('/wp/products/batch/activation/' .$option. '/'.ReadyShop_PackageManager::getApplicationToken(),array('appToken'=>ReadyShop_PackageManager::getApplicationToken(),'productArray'=>$productArray),ReadyShop::HOST_DOMAIN_SECURED,$jsonDecode);
    }
}