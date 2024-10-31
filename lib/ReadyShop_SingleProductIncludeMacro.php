<?php

namespace ReadyShop\lib;

/**
 * Class ReadyShop_SingleProductIncludeMacro
 * @package lib
 */
class ReadyShop_SingleProductIncludeMacro {

    /**
     * Class Constants
     *
     */
    //insecured domain
    const HOST_DOMAIN_INSECURE = 'http://api.readyshop.com';
    //secured domain
    const HOST_DOMAIN_SECURED = 'https://api.readyshop.com';

    /**
     * @var array $attributes
     */
    protected $attributes;

    /**
     * @var string $token
     */
    protected $token;

    /**
     * SingleProductIncludeMacro constructor.
     * @param $attributes
     * @param $token
     */
    public function __construct($attributes, $token)
    {
        $this->setAttributes($attributes);
        $this->setToken($token);
    }

    /**
     * This will return the information for a product to be displayed inline during a blog post
     * This accepts 2 parameters [product_id] and [application_token]
     * @param string $id
     * @return string
     */
    public function getMacroContent($id = null) {
        if($id === null) {
            $id = $this->getAttributes()['id'];
        }
        $html = file_get_contents(self::HOST_DOMAIN_SECURED . '/wp/products/single/include/' . $id);
        return $html;
    }



    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
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

}