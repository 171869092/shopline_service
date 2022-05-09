<?php
declare(strict_types=1);
namespace App\Service\Shopify;


use App\Service\Store\ShopifyStore;
use App\Service\Store\WcStore;
use Prophecy\Exception\Doubler\ClassNotFoundException;

class ShopifyFactory{
    public function get($name, $config = []) {
        if (is_array($name)) {
            $config = $name;
            $name = $name["platform"];
        }
        if ($name == "1" || $name == "shopify") {
            $class = ShopifyStore::class;
        } elseif ($name == "2" || $name == "wc") {
            $class = WcStore::class;
        } else {
            throw new ClassNotFoundException(sprintf('Store handle class %s not found.', $name), $name);
        }
        return make($class, [
            "storeUrl"=> $config["store_url"] ?? "",
            "apiToken"=> $config["api_token"] ?? "",
            "apiKey"=> $config["api_key"] ?? "",
            "serectKey"=> $config["serect_key"] ?? ""
        ]);
    }
}
