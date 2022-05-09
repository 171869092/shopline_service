<?php
declare(strict_types=1);
namespace App\Service\Store;

use App\Model\Store;
use App\Model\Webhook;
use Automattic\WooCommerce\Client;

class WcStore implements StoreInterface {
    private $hookUrl = [
        "coupon.created"=> "",
        "coupon.updated"=> "",
        "coupon.deleted"=> "",
        "customer.created"=> "",
        "customer.updated"=> "",
        "customer.deleted"=> "",
        "order.created"=> "",
        "order.updated"=> "",
        "order.deleted"=> "",
        "product.created"=> "",
        "product.updated"=> "",
        "product.deleted"=> "",
    ];
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $storeUrl;
    public function __construct(string $storeUrl, string $apiKey, string $serectKey)
    {
        $this->storeUrl = $storeUrl;
        $this->client = new Client("https://" . $storeUrl, $apiKey, $serectKey, ["timeout"=> 120]);
    }
    /**
     * @return Client
     */
    public function getSdk() :Client
    {
        return $this->client;
    }

    /**
     * 重新拉起WebHook
     * @param $hook
     */
    public function refreshHook($hook) {
        $web = null;
        if (!empty($hook["web_hook_id"])) {
            $web = $this->client->get("webhooks/" . $hook["web_hook_id"]);
        }
        if (!$web) {
            $web = $this->client->post("webhooks", [
                "name"=> "Fbali " . $hook["topic"],
                "topic"=> $hook["topic"],
                "delivery_url"=> $this->hookUrl[$hook["topic"]],
            ]);
            if (!empty($hook["id"])) {
                Webhook::where("id", $hook["id"])->update(["web_hook_id" => $web->id]);
            } else {
                Webhook::insert([
                    "web_hook_id"=> $web->id,
                    "topic"=> $hook["topic"],
                    "store_url"=> $this->storeUrl,
                    "create_time"=> date("Y-m-d H:i:s"),
                    "uid"=> $hook["uid"] ?? 0,
                ]);
            }
        } elseif ($web->status != "active" || $web->delivery_url != $this->hookUrl[$hook["topic"]]) {
            // 启用Hook
            $this->client->put("webhooks/" . $hook["web_hook_id"], [
                "status"=> "active",
                "delivery_url"=> $this->hookUrl[$hook["topic"]],
            ]);
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
            $web = $this->client->post("webhooks", [
                "name"=> "Fbali " . $value,
                "topic"=> $value,
                "delivery_url"=> $this->hookUrl[$value],
            ]);
            $li[] = [
                'web_hook_id'=> $web->id,
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
     * 转换数组
     * @param $result
     * @return mixed
     */
    private function toArray($result) {
        return @json_decode(@json_encode($result), true);
    }
}
