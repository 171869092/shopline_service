<?php
declare(strict_types=1);

namespace App\Service\Cart;

use App\Model\ShoppingCart;
use Hyperf\Di\Annotation\Inject;

class CartService{

    /**
     * @Inject
     * @var ShoppingCart
     */
    protected $model;

    /**
     * 添加购物车
     * @param array $params
     * @return array
     */
    public function addCard(array $params) :array
    {
        return [];
    }

    /**
     * 删除购物车
     * @param array $id
     * @return bool
     */
    public function delCard(array $ids) :bool
    {
        foreach ($ids as $id){

        }
        return true;
    }

    /**
     * get list
     * @param array $params
     * @return array
     */
    public function getList(array $params, int $uid) :array
    {
        $where = ['user_id' => $uid];
        if (isset($params['phone']) && !empty($params['phone'])){
            $where['phone'] = (int) $params['phone'];
        }
        $count = $this->model->where($where)->count();
        if (!$count){
            return ['data' => [], 'count' => 0];
        }
        $data = $this->model->where($where)->get()->toArray();
        return ['data' => $data, 'count' => $count];
    }

    /**
     * recharge
     * @param array $params
     * @return bool
     */
    public function rechargeCard(array $params) :bool
    {
        if (!isset($params['phone']) || empty($params['phone'])){
            throw new \Exception('参数错误');
        }
        return true;
    }

    /**
     * bind card
     * @param array $params
     * @return bool
     */
    public function bindCard(array $params) :bool
    {
        return true;
    }

    /**
     * channged sm card packages
     * @param array $params
     * @return bool
     */
    public function channgedPackages(array $params) :bool
    {
        return true;
    }

    public function queryTelephone(array $params) :bool
    {
        return true;
    }

    public function upStatus() :bool
    {
        return true;
    }

    public function unify(array $params) :array
    {

    }
}
