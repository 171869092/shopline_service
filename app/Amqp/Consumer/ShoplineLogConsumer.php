<?php

declare(strict_types=1);

namespace App\Amqp\Consumer;

use App\Common\Request;
use App\Model\Store;
use Hyperf\Amqp\Result;
use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerMessage;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * @Consumer(exchange="hyperf", routingKey="hyperf", queue="hyperf", name="ShoplineLogConsumer", nums=1, enable=true)
 */
#[Consumer(exchange: 'hyperf', routingKey: 'hyperf', queue: 'hyperf', name: "ShoplineLogConsumer", nums: 1)]
class ShoplineLogConsumer extends ConsumerMessage
{
    public function consumeMessage($data, AMQPMessage $message): string
    {
        echo "\r\n~~~~ 这里是推送到shopline运单号 ~~~ \r\n";
        if (isset($data['number']) && !empty($data['number']))
        {
            $uri = 'https://'.$data['handle'].'.myshopline.com';
            $path = '/admin/openapi/v20210901/orders/:order_id/fulfillments/:fulfillment_id/update_tracking.json';
            $push = [
                'company' => $data['company'],
                'number' => $data['number'],
                'url' => $data['url']
            ];
            $store = Store::where(['store_name' => $data['handle']])->select(['token'])->first();
            if (!$store->token){
                print_r('没有token');
                return Result::ACK;
            }
            (new Request())->fulfillment($uri,$path,$push,$store->token);
            echo "\r\n ~~ 推送shopline成功 ~~ \r\n";
        }
        return Result::ACK;
    }

    public function isEnable(): bool
    {
        return parent::isEnable();
    }
}
