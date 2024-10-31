<?php
/**
 * @author Daniel P. Baddeley JR <dan@bluewatermedia.tv>
 * @date: 9/15/17 2:09 PM
 * @info This service is used to communicate with the Ready Shop API [Service Layer]
 */

namespace ReadyShop\lib;

require_once 'ReadyShop.php';
use ReadyShop\lib\ReadyShop as ReadyShop;

/**
 * Class ReadyShop_APIService
 * @package lib
 */
class ReadyShop_APIService
{


    /**
     * To be used for $_POST requests against the ReadyShop API [Service Layer]
     * @param $path
     * @param $host
     * @param $body
     * @param $jsonDecode
     * @return array
     */
    public static function post($path,  array $body, $host = ReadyShop::HOST_DOMAIN_SECURED, $jsonDecode = true) {
        //make post request
        $request = wp_remote_post($host . $path, array('body' => $body));


        //check request response
        if ( !is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
            $response = $request['body'];
        } else {
            $response = null;
        }

        if($jsonDecode && $response !== null) {
            $response = json_decode($response,true);
        }

        return $response;
    }

    /**
     * To be used for $_GET requests against the ReadyShop API [Service Layer]
     *
     * @param $path
     * @param $host
     * @param $jsonDecode
     * @return array|mixed
     */
    public static function get($path, $host = ReadyShop::HOST_DOMAIN_SECURED, $jsonDecode = true) {

        $request = wp_remote_get($host . $path);

        if ( !is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
            $response = $request['body'];
        } else {
            $response = null;
        }

        if($jsonDecode && $response !== null) {
            $response = json_decode($response,true);
        }

        return $response;
    }
}