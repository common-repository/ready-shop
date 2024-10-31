<?php
/**
 * @author Daniel P. Baddeley JR <dan@bluewatermedia.tv>
 * @date: 9/19/17 2:04 PM
 * @info This service is used to fetch analytics for the shop owners dashboard to display commission & sales history
 */

namespace ReadyShop\lib;

require_once 'ReadyShop_APIService.php';

use ReadyShop\lib\ReadyShop_APIService as ReadyShop_APIService;

/**
 * Class ReadyShop_AnalyticService
 * @package lib
 */
class ReadyShop_AnalyticService
{

    /**
     * @return array
     */
    public static function getDashboardStats() {
        return ReadyShop_APIService::get('/v1.0/installation/domain/metrics/'.ReadyShop_PackageManager::getApplicationToken());
    }
}