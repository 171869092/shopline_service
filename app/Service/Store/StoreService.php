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
        $store->easy_auth_key = $params['easy_auth_key'];
        $store->easy_service_id = $params['easy_service_id'];
        if (!$store->save()){
            throw new \Exception('保存失败');
        }
        return true;
    }
}
