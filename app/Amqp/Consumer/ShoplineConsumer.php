<?php

declare(strict_types=1);

namespace App\Amqp\Consumer;

use App\Model\Order;
use App\Model\OrderPush;
use App\Model\Store;
use App\Service\EasyParcel\EasyParcelService;
use Hyperf\Amqp\Result;
use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerMessage;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * @Consumer(exchange="hyperf", routingKey="hyperf", queue="hyperf", name="ShoplineConsumer", nums=1, enable=true)
 */
#[Consumer(exchange: 'hyperf', routingKey: 'hyperf', queue: 'hyperf', name: "ShoplineConsumer", nums: 1)]
class ShoplineConsumer extends ConsumerMessage
{

    public function consumeMessage($data, AMQPMessage $message): string
    {
        echo "\r\n consume = ";
        print_r($data);
        echo "\r\n";
        #. 处理shopline订单 推送到easyparcel
        if (isset($data['shopline_id']) && !empty($data['shopline_id'])){
echo "\r\n ~~ 我进来了 ~~ \r\n";
            $shipping = json_decode($data['shipping_address'], true);
            $customer = json_decode($data['customer'], true);
            $lineIten = json_decode($data['line_item'], true)[0];
            $phone = isset($shipping['phone']) ? $shipping['phone'] : $customer['addresses']['phone'];
            $push = [
                'weight' => bcdiv(strval($data['total_weight']),'1000',2), #. 重量
                'content' => $lineIten['title'], #. 产品内容
                'value' => $lineIten['quantity'], #. 数量
                'service_id' => 'EP-CS0WO', #. 目前写死
                'pick_name' => 'Yong Tat', #. 发送人姓名
                'pick_contact' => '+65-6581175298', #. 发送人电话
                'pick_unit' => '30', #. 单位
                'pick_code' => '409015', #.邮编
                'pick_country' => 'SG', #. 发送人国家
                'send_name' => $shipping['first_name']. ' '. $shipping['last_name'], #. 收件人姓名
                'send_contact' => '+65-'.$phone, #. 收件人电话
                'send_unit' => '20', #. 收件 单位
                'send_addr1' => $shipping['address1']. ' '. $shipping['address2'], #. 收件人地址
                'send_state' => 'png', #. 收件状态
                'send_code' => $shipping['zip'],#. 收件人邮编
                'send_country' => $shipping['country_code'], #. 收件人国家
                'collect_date' => date('Y-m-d'), #. 时间
                'sms' => '1',
                'reference' => $data['shopline_id']
            ];
            $result = (new EasyParcelService())->mPSubmitOrderBulk($push);
            if ((isset($result['api_status']) && $result['api_status'] == 'Success') && $result['result'][0]['status'] != 'fail'){
echo "\r\n ~~ 推送成功了 ~~ \r\n";
                $msg = '推送成功';
                $type = 1;

            }else{
                $msg = '推送失败';
                $type = -1;
echo "\r\n ~~ 推送失败了 ~~ \r\n";
            }
            $store = Store::where(['shopline_id' => $data['store_id']])->first();
            Order::where(['shopline_id' => $data['shopline_id']])->update(['is_exec' => 2, 'update_time' => date('Y-m-d H:i:s')]);
            OrderPush::insert([
                'order_id' => $data['shopline_id'],
                'msg' => $msg,
                'handle' => $store->store_name,
                'push_time' => date('Y-m-d H:i:s'),
                'type' => $type,
                'params' => json_encode($push),
                'return_value' => json_encode($result)
            ]);
echo "\r\n ~~ 添加成功push log ~~ \r\n";
        }
        return Result::ACK;
    }

    public function isEnable(): bool
    {
        return parent::isEnable();
    }
}
