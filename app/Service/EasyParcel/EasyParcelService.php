<?php
namespace App\Service\EasyParcel;
use App\Amqp\Producer\ShoplineLogProducer;
use App\Model\EasyWebhook;
use App\Model\OrderPush;
use App\Model\Service;
use App\Model\Store;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Hyperf\Amqp\Producer;
use Hyperf\Config\Annotation\Value;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\Utils\Coroutine;

class EasyParcelService
{
    /**
     * @Value("easyparcel.dev.api_key")
     */
    protected $appKey;

    /**
     * @Value("easyparcel.dev.auth_key")
     */
    protected $authKey;

    /**
     * @Value("easyparcel.dev.uri")
     */
    protected $uri;

    /**
     * @Inject
     * @var Store
     */
    protected $storeModel;

    /**
     * @Inject
     * @var Service
     */
    protected $serviceModel;

    /**
     * @Inject
     * @var Producer
     */
    protected $producer;

    public function request(string $path, array $params) :array
    {
        $uri = $this->uri;
        $result = parallel([
            function () use($uri,$path, $params)
            {
                $client = new Client([
//                    'base_uri' => $uri,
                    'handler' => HandlerStack::create(new CoroutineHandler()),
                    'timeout' => 5,
                    'swoole' => [
                        'timeout' => 10,
                        'socket_buffer_size' => 1024 * 1024 * 2
                    ]
                ]);
                $params = json_encode($params);
                echo "params = \r\n";
                print_r($params);
                echo "\r\n url = ". $uri.$path . "\r\n";
                $respone = $client->post($uri.$path, ['body' => $params]);
                return [
                    'coroutine_id' => Coroutine::id(),
                    'code' => $respone->getStatusCode(),
                    'body' => $respone->getBody()->getContents(),
                    'content' => $respone->getReasonPhrase()
                ];
            }
        ]);
        echo "respone = \r\n";
        print_r($result);
        $body = [];
        if ($result[0]['body']){
            $body = json_decode($result[0]['body'], true);
        }
        return $body;
    }

    /**
     * 下单前先获取下
     */
    public function mPRateCheckingBulk(array $params)
    {
    }

    /**
     * 下单easyparcel
     * @return bool
     */
    public function mPSubmitOrderBulk(array $data = []) :array
    {
        if (!$data) return [];
        $path = '/?ac=MPSubmitOrderBulk';
//        $data = [
//            'weight' => '0.1', #. 重量
//            'content' => '2017-09-14 - book', #. 产品内容
//            'value' => '1', #. 数量
//            'service_id' => 'EP-CS0WO', #. 目前写死
//            'pick_name' => 'Yong Tat', #. 发送人姓名
//            'pick_contact' => '+65-6581175298', #. 发送人电话
//            'pick_unit' => '30', #. 单位
//            'pick_code' => '409015', #.邮编
//            'pick_country' => 'SG', #. 发送人国家
//            'send_name' => 'Sam', #. 收件人姓名
//            'send_contact' => '+65-93849000', #. 收件人电话
//            'send_unit' => '20', #. 收件 单位
//            'send_addr1' => 'ssssadsasdst test', #. 收件人地址
//            'send_state' => 'png', #. 收件状态
//            'send_code' => '409015',#. 收件人邮编
//            'send_country' => 'SG', #. 收件人国家
//           'collect_date' => '2022-05-18', #. 时间
//            'sms' => '1'
//        ];
        $params = [
            'authentication' => $this->authKey,
            'api' => $this->appKey,
            'bulk' => [$data]
        ];
        $result = $this->request($path, $params);
        if (!isset($result['api_status']) && $result['api_status'] != 'Success'){
            throw new \Exception('下单EasyParcel失败');
        }
        return $result;
    }

    /**
     * 处理easyParcel的webhook数据
     * 将数据先落地一份，在回传到shopline
     * @param array $array
     * @return bool
     */
    public function webhook(array $array) :bool
    {
        EasyWebhook::insert([
            'topic' => 'shipment/create',
            'create_time' => date('Y-m-d H:i:s'),
            'payload' => json_encode($array)
        ]);
        #. 这里应该是往shopline推送运单号
        $this->producer->produce(new ShoplineLogProducer($array));
        return true;
    }

    /**
     * 测试easyParcel连接
     * @param string $api
     * @param string $auth
     * @return bool
     */
    public function testConnect(string $api, string $auth) :bool
    {
        $params = [
            'authentication' => $auth,
            'api' => $api,
            'bulk' => [
                [
                    'pick_code' => '409015',
                    'pick_state' => 'sgr',
                    'pick_country' => 'SG',
                    'send_code' => '059897',
                    'send_state' => 'sgr',
                    'send_country' => 'SG',
                    'weight' => '14'
                ]
            ]
        ];
        $uri = 'http://connect.easyparcel.sg';
        $path = '/?ac=MPRateCheckingBulk';
        $client = new Client([
            'base_uri' => $uri,
            'handler' => HandlerStack::create(new CoroutineHandler()),
            'timeout' => 5,
            'swoole' => [
                'timeout' => 10,
                'socket_buffer_size' => 1024 * 1024 * 2
            ]
        ]);
        $respone = $client->post($uri.$path, ['body' => json_encode($params)]);
        $result = $respone->getBody()->getContents();
        $result = json_decode($result, true);
        if (isset($result['api_status']) && $result['api_status'] == 'Success'){
            return true;
        }
        return false;
    }

    /**
     * 获取easyparcel的service列表
     * @return array
     */
    public function getServiceList() :array
    {
        return Service::get()->toArray();
    }

    /**
     * 获取配置
     * @param string $handle
     * @return array
     */
    public function  getConfig(string $handle) :array
    {
        $store = $this->storeModel->where(['store_name' => $handle])->select(['id','easy_api','easy_auth_key','easy_service_id'])->first();
        $serviceList = $this->serviceModel->get();
        return ['data' => $store->toArray(), 'service' => $serviceList->toArray()];
    }

    /**
     * 推送记录列表
     * @param string $handle
     * @param int $limit
     * @param int $size
     * @return array
     */
    public function getPushLog(string $handle, int $limit = 10, int $page = 0) :array
    {
        $model = OrderPush::query();
        $count = $model->where(['handle' => $handle])->count();
        $data = $model
            ->where(['handle' => $handle])
            ->limit($limit)->offset($page)
            ->orderBy('id','desc')
            ->get()->toArray();
        return ['count' => $count, 'data' => $data];
    }
}
