<?php


namespace App\Listener;

use App\Collector\SocketUserCollector;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeServerStart;

/**
 * Class AfterReplyListener
 * @Listener()
 * @package App\Listener
 */
class InitializeListener implements ListenerInterface
{
    public function listen(): array
    {
        // 返回一个该监听器要监听的事件数组，可以同时监听多个事件
        return [
            BeforeServerStart::class
        ];
    }

    public function process(object $event)
    {
        if ($event->serverName == "socket-io") {
            SocketUserCollector::clear();
        }
    }
}