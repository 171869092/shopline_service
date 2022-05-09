<?php
declare(strict_types=1);
namespace App\Service\Paypal;

use Hyperf\Logger\Logger;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payment;
use Psr\Container\ContainerInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * Class PaypalService
 * @package App\Service\Paypal
 */
class PaypalService{
    /**
     * @var PaypalService
     */
    private $server;

    /**
     * @var Item
     */
    private $item;

    /**
     * @var ItemList
     */
    private $itemList;

    /**
     * @var Payment
     */
    private $payment;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @Inject
     * @var ContainerInterface
     */
    private $container;

    protected function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * 初始化
     */
    protected function init() :PaypalService{
        new Item();
        new ItemList();
        return $this;
    }

    /**
     * create
     * @return Payment
     */
    public function create() :Payment
    {
        $this->logger->info("\r\n 支付开始 \r\n");
        $this->payment
            ->setIntent()
            ->setPayer()
            ->setRedirectUrls()
            ->setTransactions()
        ;
        return $this->payment->create();
    }
}
