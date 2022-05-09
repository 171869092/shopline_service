<?php
declare(strict_types=1);
namespace App\Service\Order;
use App\Model\OrderLog;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
class OrderService
{
    /**
     * @var
     */
    protected $model;

    /**
     * @var
     */
    protected $item;

    /**
     * @Inject
     * @var OrderLog
     */
    protected $orderLog;

    /**
     * @var
     */
    protected $cartSerive;

    /**
     * 分单
     * @param array $params
     * @return array
     */
    public function splitOrder(array $params) :array
    {
        return [];
    }

    /**
     * 支付
     * @param array $params
     * @return array
     */
    public function pay(array $params) :array
    {
        return [];
    }
}
