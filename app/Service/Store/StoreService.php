<?php
declare(strict_types=1);
namespace App\Service\Store;
use App\Model\Store;
use Hyperf\Di\Annotation\Inject;
use App\Model\Log;

class StoreService{

    /**
     * @Inject
     * @var Store
     */
    protected $storeModel;
    public function ex(array $params) :bool
    {
        if ($params) {
            Log::insert(['log' => json_encode($params)]);
        }
        return true;
    }

    /**
     * 保存店铺配置
     * @param array $params
     * @return bool
     */
    public function saveStore(array $params) :bool
    {
        $store = $this->storeModel->where(['store_name' => $params['handle']])->first();
        if (!$store || !$store->exists()){
            throw new \Exception('未找到店铺信息');
        }
        $store->easy_api = $params['easy_api'];
        $store->easy_auth_key = 'XSsMotkzD6'; //. 这里可以写死了！ 全部都是一样的auth key
        $store->easy_service_id = $params['easy_service_id'];
        $store->update_time = date('Y-m-d H:i:s');
        $store->sync_status = 2; #.设置了信息后 把同步状态改为开始同步
        if (!$store->save()){
            throw new \Exception('保存失败');
        }
        return true;
    }

    public function pushShopline() :bool
    {

    }

    /**
     * 更新店铺信息
     * @param array $params
     * @return bool
     */
    public function updateStore(array $params, string $handle) :bool
    {
        if (!isset($params['update_time'])){
            $params['update_time'] = date('Y-m-d H:i:s');
        }
        $result = $this->storeModel->where(['store_name' => $handle])->update($params);
        return $result ? true : false;
    }
}
