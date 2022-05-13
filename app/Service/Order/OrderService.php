<?php
declare(strict_types=1);
namespace App\Service\Order;
use App\Amqp\Producer\ShoplineProducer;
use App\Model\OrderLog;
use Hyperf\Amqp\Producer;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
class OrderService
{
    /**
     */
    protected $model;

    /**
     * @Inject
     * @var OrderLog
     */
    protected $orderLog;

    /**
     * @Inject
     * @var Producer
     */
    protected $producer;

    public function webHook(array $params) :bool
    {
        return true;
    }

    public function getShoplineOrder(string $handle) :bool
    {
        $url = 'https://'. $handle .'.myshopline.com/admin/openapi/v20210901/orders.json';

        return true;
    }

    /**
     * push queue
     * @param array $array
     * @return bool
     */
    public function pushQueue(array $array) :bool
    {
        return $this->producer->produce(new ShoplineProducer($array));
    }
}
