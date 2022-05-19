<?php
namespace App\Service\EasyParcel;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
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
}
