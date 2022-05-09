<?php


namespace App\Crontab;

use App\Model\Store;
use App\Model\Webhook;
use App\Service\Shopify\ShopifyFactory;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Logger\LoggerFactory;

/**
 * Class HookGoods
 * @package App\Crontab
 */
class HookGoods
{
    /**
     * @Inject
     * @var LoggerFactory
     */
    private $loggerFactory;
    /**
     * @Inject
     * @var ShopifyFactory
     */
    private $storeFactory;
    /**
     * @Crontab(rule="0 * * * *", memo="监听店铺网络钩子", name="HookGoods_store", enable=false)
     */
    public function store(string $storeUrl = null) {
        $logger = $this->loggerFactory->get("HookGoods_store", "crontab");
        // 可自动创建钩子
        $ext = [
            "1"=> [
                "app/uninstalled",
            ],
            "2"=> []
        ];
        $storeList = Store::where("is_del", 1)
            ->where(function ($query) {
                $query->where("api_token", "!=", "")
                    ->orWhere(function ($query) {
                        $query->where("api_key", "!=", "")
                            ->where("serect_key", "!=", "");
                    });
            })
            ->when($storeUrl, function ($query, $storeUrl) {
                return $query->where("store_url", $storeUrl);
            })
            ->get()
            ->toArray();
        foreach ($storeList as $store) {
            try {
                $logger->info("同步店铺({$store["store_url"]})钩子");
                if ($store["platform"] == "1" && empty($store["api_token"])) {
                    continue;
                }
                if ($store["platform"] == "2" && (empty($store["api_key"]) || empty($store["serect_key"]))) {
                    continue;
                }
                $hookRows = Webhook::where("store_url", $store["store_url"])->get()->toArray();
                foreach (array_diff($ext[$store["platform"]] ?? [], array_column($hookRows, "topic")) as $topic) {
                    $hookRows[] = [
                        "topic"=> $topic,
                        "uid"=> $store["user_id"],
                    ];
                }
                $storeSdk = $this->storeFactory->get($store);
                foreach ($hookRows as $hook) {
                    $storeSdk->refreshHook($hook);
                }
            } catch (\Exception $exception) {
                $logger->error($exception->getMessage(), $exception->getTrace());
            }
        }
    }
}