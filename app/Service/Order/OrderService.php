<?php
declare(strict_types=1);
namespace App\Service\Order;
use App\Model\Order;
use App\Model\OrderItem;
use App\Model\OrderLog;
use App\Model\ShoppingCart;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
class OrderService
{
    /**
     * @Inject
     * @var Order
     */
    protected $model;

    /**
     * @Inject
     * @var OrderItem
     */
    protected $item;

    /**
     * @Inject
     * @var OrderLog
     */
    protected $orderLog;

    /**
     * @Inject
     * @var ShoppingCart
     */
    protected $cartSerive;

    /**
     * 创建订单
     * @param array $params
     * @return array
     */
    public function unifyOrder(array $params) :array
    {
        if (!isset($params['ids']) || empty($params['ids'])){
            throw new \Exception('参数错误');
        }
        $cart = $this->cartSerive->where(['id' => ['in', $params['ids']]])->first();
        if (!$cart){
            throw new \Exception('未找到购物车数据');
        }
        Db::beginTransaction();
        $this->model->create();
        $this->item->create(['order_id' => $this->model->id]);
        $this->orderLog->create(['order_id' => $this->model->id]);
        Db::commit();
        return [];
    }

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
