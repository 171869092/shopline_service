<?php

namespace App\Service\Store;

use Automattic\WooCommerce\Client;
use PHPShopify\ShopifySDK;

/**
 * Class Store
 * @package App\Service\Store
 */
interface StoreInterface
{
    /**
     * 获取SDK
     * @return ShopifySDK|Client
     */
    public function getSdk();
    /**
     * 重新拉起WebHook
     * @param $hook
     */
    public function refreshHook($hook);
    /**
     * @param string|array $topic
     * @return bool
     */
    public function createHook($topic);
}
