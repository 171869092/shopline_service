<?php
declare(strict_types=1);
namespace App\Service\Store;

use App\Model\Store;
use App\Model\Webhook;
use PHPShopify\Exception\ApiException;
use PHPShopify\ShopifySDK;
use Hyperf\Di\Annotation\Inject;

class ShopifyStore implements StoreInterface {
    private $hookUrl = [
        "app/uninstalled"=> "https://api.fbali.co/site/delete",
        "products/create"=> "",
        "products/update"=> "",
        "products/delete"=> "",
        "orders/paid"=> "",
        "orders/edited"=> "",
        "orders/updated"=> "",
        "refunds/create"=>"",
    ];
    /**
     * @var string
     */
    private $storeUrl;
    /**
     * @var ShopifySDK
     */
    private $shopify;
    public function __construct(string $storeUrl, string $apiToken)
    {
        $this->storeUrl = $storeUrl;
        // $app_access = InstallApp::where(['shop'=> $storeUrl])->first();

        $shopifyConfig = [
            'ShopUrl'=> trim($storeUrl),
            'AccessToken'=> $apiToken, // trim($app_access['token']),
            'ApiVersion' => '2020-10'
        ];
        $this->shopify = new ShopifySDK($shopifyConfig);
    }

    public function getSdk() :ShopifySDK
    {
        // TODO: Implement getSdk() method.
        return $this->shopify;
    }

    /**
     * 重新拉起WebHook
     * @param $hook
     * @throws ApiException
     * @throws \PHPShopify\Exception\CurlException
     */
    public function refreshHook($hook) {
        try {
            if (empty($hook["web_hook_id"])) throw new ApiException("Not Found", 404);
            $this->shopify->Webhook($hook["web_hook_id"])->get();
        } catch (ApiException $apiException) {
            $code = $apiException->getCode();
            if ($code == 404) {
                // 重新创建钩子
                $web = $this->createWebHook($hook["topic"]);
                if (!empty($hook["id"])) {
                    Webhook::where("id", $hook["id"])->update(["web_hook_id" => $web["id"]]);
                } else {
                    Webhook::insert([
                        "web_hook_id"=> $web["id"],
                        "topic"=> $hook["topic"],
                        "store_url"=> $this->storeUrl,
                        "create_time"=> date("Y-m-d H:i:s"),
                        "uid"=> $hook["uid"] ?? 0,
                    ]);
                }
            } else {
                // 402 不可用的店铺 401 没有权限（token过期）
                throw $apiException;
            }
        }
    }

    /**
     * @param string|array $topic
     * @return bool
     */
    public function createHook($topic) {
        if (is_string($topic)) {
            $topic = [$topic];
        }
        if (!is_array($topic)) {
            return false;
        }
        $user_id = Store::where("store_url", $this->storeUrl)->value("user_id");
        $li = [];
        foreach ($topic as $value) {
            $web = $this->createWebHook($value);
            $li[] = [
                'web_hook_id'=> $web['id'],
                'topic' => $value,
                'create_time'=> date('Y-m-d H:i:s'),
                'store_url' => $this->storeUrl,
                'uid'=> $user_id
            ];
        }
        Webhook::insert($li);
        return true;
    }

    /**
     * 创建钩子
     * @param $topic
     * @return array|bool|bool[]|mixed|null
     * @throws ApiException
     * @throws \PHPShopify\Exception\CurlException
     */
    private function createWebHook($topic) {
        try {
            $web = $this->shopify->Webhook->post(['topic'=> $topic, 'address'=> $this->hookUrl[$topic], 'format'=>'json']);
        } catch (ApiException $apiException) {
            if ($apiException->getCode() == 422) {
                // 已存在
                $hook = $this->shopify->Webhook->get(['topic'=> $topic, 'address'=> $this->hookUrl[$topic]]);
                if (is_array($hook)) {
                    $web = reset($hook);
                } else {
                    throw $apiException;
                }
            } else {
                throw $apiException;
            }
        }
        return $web;
    }
}
