<?php


namespace App\Listener;

use App\Controller\WebSocket\Chat;
use App\Event\GuestActionEvent;
use App\Model\GuestUser;
use App\Service\GuestManager\GuestInfoService;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\SocketIOServer\SocketIO;
use Hyperf\Utils\ApplicationContext;

/**
 * Class GuestInfoListener
 * @Listener()
 * @package App\Listener
 */
class GuestInfoListener implements ListenerInterface
{
    public function listen(): array
    {
        // 返回一个该监听器要监听的事件数组，可以同时监听多个事件
        return [
            GuestActionEvent::class,
        ];
    }

    public function process(object $event)
    {
        try {
            $guestUser = GuestUser::where("id", $event->guestId)->first();
            $guestInfo = ApplicationContext::getContainer()->get(GuestInfoService::class);
            switch ($event->target) {
                case GuestActionEvent::ONLINE_EVENT:
                case GuestActionEvent::OFFLINE_EVENT:
                case GuestActionEvent::WORKMAN_EVENT:
                case GuestActionEvent::USER_READ_EVENT:
                    // 更新消息列表
                    if ($guestUser) {
                        [$guest, $unread_count] = $guestInfo->catalog($guestUser["user_id"]);
                        $socketIO = ApplicationContext::getContainer()->get(SocketIO::class);
                        $socketIO->of(Chat::NAMESPACE)->in("1_" . $guestUser["user_id"])->emit("visitor", compact("guest", "unread_count"));
                    }
                    break;
                case GuestActionEvent::CONSULT_EVENT:
                    // 游客咨询
                    $guestInfo->trace($event->guestId, $guestUser ? $guestUser["referer"] : "", 2);
                    break;
            }
        } catch (\Throwable $throwable) {
            // 异常
            var_dump($throwable->getMessage());
        }
    }
}