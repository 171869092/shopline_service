<?php

declare(strict_types=1);

namespace App\Amqp\Consumer;

use App\Model\Order;
use App\Model\OrderPush;
use App\Model\Service;
use App\Model\Store;
use App\Service\EasyParcel\EasyParcelService;
use Hyperf\Amqp\Result;
use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerMessage;
use PhpAmqpLib\Message\AMQPMessage;
use Hyperf\Di\Annotation\Inject;

/**
 * @Consumer(exchange="hyperf", routingKey="hyperf", queue="hyperf", name="ShoplineConsumer", nums=1, enable=true)
 */
#[Consumer(exchange: 'hyperf', routingKey: 'hyperf', queue: 'hyperf', name: "ShoplineConsumer", nums: 1)]
class ShoplineConsumer extends ConsumerMessage
{
    /**
     * @Inject
     * @var Store
     */
    protected $stores;
    /**
     * @Inject
     * @var Service
     */
    protected $services;

    public function consumeMessage($data, AMQPMessage $message): string
    {
        echo "\r\n consume = ";
        print_r($data);
        echo "\r\n";
        $msg = '';
        #. 处理shopline订单 推送到easyparcel
        if (isset($data['shopline_id']) && !empty($data['shopline_id'])){
            echo "\r\n ~~ 我进来了 ~~ \r\n";
            $shipping = json_decode($data['shipping_address'], true);
            $customer = json_decode($data['customer'], true);
            $lineIten = json_decode($data['line_item'], true)[0];
            $phone = isset($shipping['phone']) ? $shipping['phone'] : $customer['addresses']['phone'];
            //. 获取service id
            $store = $this->stores->where(['shopline_id' => $data['store_id']])->first();
            #. 先判断国家是否是 设置的service id 支持的
            if (isset($store->easy_service_id) && !empty($store->easy_service_id)){
                $serviceData = $this->services->where(['service_id' => $store->easy_service_id])->first();
                if (!$serviceData){
                    //.根据service id在service表获取数据，如果则直接取对应国家默认一个
                    $country = $this->services->where(['country' => $shipping['country_code']])->first();
                    if (!$country){
                        echo "\r\n 获取对应国家的service id错误 --- 准备退出队列了 \r\n";
                        $msg = ' 获取对应国家的service id错误';
                    }
                    //. 直接覆盖
                    $store->easy_service_id = $country->service_id;

                }else{
                    //. 如果有 就要判断 该service id 是否支持当前要配送的国家
                    if (!isset($serviceData->country)){
                        echo "\r\n 国家数据错误 \r\n";
                        $msg = '国家数据错误';
                    }
                    if ($serviceData->country != $shipping['country_code']){
                        //. 如果不等于要配送的国家 则取默认支持该国家的servide id
                        $country = $this->services->where(['country' => $shipping['country_code']])->first();
                        if (!$country){
                            echo "\r\n 获取对应国家的service id错误 --- 准备退出队列了 \r\n";
                            $msg = ' 获取对应国家的service id错误';
                        }
                        //. 覆盖掉service id
                        $store->easy_service_id = $country->service_id;
                    }
                }
            }
            $are = $shipping['country_code'] == 'SG' ? '+65-' : '';
            $push = [
                'weight' => bcdiv(strval($data['total_weight']),'1000',2), #. 重量
                'content' => $lineIten['title'], #. 产品内容
                'value' => $lineIten['quantity'], #. 数量
//                'service_id' => 'EP-CS0WO', #. 目前写死
                'service_id' => $store->easy_service_id, #. 目前写死
//                'pick_name' => 'Yong Tat', #. 发送人姓名
                'pick_name' => $store->easy_send_first_name .' '. $store->easy_send_last_name, #. 发送人姓名
//                'pick_contact' => '+65-6581175298', #. 发送人电话
                'pick_contact' => $store->easy_send_phone, #. 发送人电话
                'pick_addr1' => $store->easy_address, #. 发送人地址
                'pick_addr2' => '',
                'pick_addr3' => '',
                'pick_unit' => '', #. 单位
//                'pick_code' => '409015', #.邮编
                'pick_code' => $store->easy_post_code, #.邮编
                'pick_country' => 'SG', #. 发送人国家
                'send_name' => $shipping['first_name']. ' '. $shipping['last_name'], #. 收件人姓名
                'send_contact' => $are .$phone, #. 收件人电话
                'send_unit' => '', #. 收件 单位
                'send_addr1' => $shipping['address1']. ' '. $shipping['address2'], #. 收件人地址
                'send_addr2' => '',
                'send_addr3' => '',
                'send_city' => $shipping['city'] ?? '',
                'send_state' => $shipping['country_code'], #. 不知道啥玩意
                'send_code' => $shipping['zip'],#. 收件人邮编
                'send_country' => $shipping['country_code'], #. 收件人国家
                'collect_date' => date('Y-m-d'), #. 时间
                'sms' => '1',
                'reference' => $data['shopline_id']
            ];
            $insert = [
                'order_id' => $data['shopline_id'],
                'msg' => $msg,
                'handle' => $store->store_name,
                'push_time' => date('Y-m-d H:i:s'),
                'type' => 0,
                'params' => json_encode($push),
                'return_value' => ''
            ];
            //. 只要$msg 为空说明没错误才触发推送
            if (!$msg){
                $result = (new EasyParcelService())->mPSubmitOrderBulk($push,$store->easy_auth_key, $store->easy_api);
                if ((isset($result['api_status']) && $result['api_status'] == 'Success') && $result['result'][0]['status'] != 'fail'){
                    echo "\r\n ~~ 推送成功了 ~~ \r\n";
//                    $msg = '推送成功';
                    $insert['msg'] = '推送成功';
                    $insert['type'] = 1;
//                    $type = 1;

                }else{
                    echo "\r\n ~~ 推送失败了 ~~ \r\n";
                    $insert['msg'] = '推送失败';
                    $insert['type'] = -1;
//                    $msg = '推送失败';
//                    $type = -1;
                }
                $insert['return_value'] = json_encode($result);
                Order::where(['shopline_id' => $data['shopline_id']])->update(['is_exec' => 2, 'update_time' => date('Y-m-d H:i:s')]);
            }else{
                //. 这里就不推送了, 但是需要记录日志
                $insert['type'] = -1;
            }

            OrderPush::insert($insert);
            echo "\r\n ~~ 添加成功push log ~~ \r\n";
        }
        return Result::ACK;
    }

    public function isEnable(): bool
    {
        return parent::isEnable();
    }
}
