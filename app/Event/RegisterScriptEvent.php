<?php
declare(strict_types=1);
namespace App\Event;
use App\Service\Shopify\ShopifyFactory;
use Hyperf\Config\Annotation\Value;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Logger\LoggerFactory;

class RegisterScriptEvent{
    /**
     * @Value("shopify.host")
     */
    public $host;

    /**
     * @Value("shopify.script")
     */
    public $script;

    /**
     * option
     * @var array
     */
    public $option;

    /**
     * @var ShopifyFactory
     */
    public $shopify;

    /**
     * @var LoggerFactory
     */
    public $logger;

    /**
     * @var string
     */
    public $storeUrl;


    public function __construct(string $store, string $token)
    {
        $this->option = [
            'script' => $this->script,
            'host' => $this->host
        ];
        $this->shopify = $this->shopify->get('shopify',[
            'token' => $token,
            'store' => $store
        ]);
        $this->logger = $this->logger->get('register_script');
        $this->storeUrl = $store;
    }
}
