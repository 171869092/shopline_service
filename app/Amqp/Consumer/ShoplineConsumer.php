<?php

declare(strict_types=1);

namespace App\Amqp\Consumer;

use Hyperf\Amqp\Result;
use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerMessage;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * @Consumer(exchange="hyperf", routingKey="hyperf", queue="hyperf", name="ShoplineConsumer", nums=1)
 */
#[Consumer(exchange: 'hyperf', routingKey: 'hyperf', queue: 'hyperf', name: "ShoplineConsumer", nums: 1)]
class ShoplineConsumer extends ConsumerMessage
{
    public function consumeMessage($data, AMQPMessage $message): string
    {
        echo "\r\n consume = ";
        print_r($data);
        echo "\r\n";
        return Result::ACK;
    }
}
