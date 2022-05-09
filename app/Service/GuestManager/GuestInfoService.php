<?php


namespace App\Service\GuestManager;

use App\Collector\SocketUserCollector;
use App\Controller\WebSocket\Chat;
use App\Model\Dialogue;
use App\Model\GuestTrace;
use App\Model\GuestUser;
use App\Model\Store;
use App\Model\User;
use App\Service\User\UserService;
use Hyperf\SocketIOServer\SocketIO;
use Hyperf\Utils\ApplicationContext;

/**
 * Class GuestInfoService
 * @package App\Service\GuestManager
 */
class GuestInfoService
{
    /**
     * 获取游客列表（未读数量）
     * @param $user_id
     * @return array
     */
    public function catalog($user_id)
    {
        $guest = GuestUser::select("*")
            ->selectSub(
                Dialogue::selectRaw("count(*)")
                    ->where(["user_id" => $user_id, "user_read" => 0])
                    ->whereColumn('guest_user.id', 'dialogue.guest_id')
                , "unread_count")
            ->where(["user_id" => $user_id]) // , "is_contact" => 1
            ->get()
            ->toArray();
        $online = [];
        $unread = [];
        $id = [];
        $time_zone = User::where("id", $user_id)->value("time_zone") ?: "Asia/Shanghai";
        foreach ($guest as &$item) {
            // 获取在线状态
            $item["is_online"] = SocketUserCollector::hasUser(2, $item["id"]) ? 1 : 2;
            $online[] = $item["is_online"];
            $unread[] = $item["unread_count"];
            $id[] = $item["id"];
            // 最新一条消息
            /** @var Dialogue $dialogue */
            $dialogue = Dialogue::where(["user_id"=> $item["user_id"], "guest_id"=> $item["id"]])->orderBy("id", "desc")->first();
            $item["latest_news"] = null;
            if ($dialogue) {
                $dialogue->create_time->setTimezone(new \DateTimeZone($time_zone));
                $dialogue->update_time->setTimezone(new \DateTimeZone($time_zone));
                $item["latest_news"] = $dialogue->toArray();
            }
            $item["create_time"] = date_create($item["create_time"])->setTimezone(new \DateTimeZone($time_zone))->format("Y-m-d H:i:s");
            $item["update_time"] = date_create($item["update_time"])->setTimezone(new \DateTimeZone($time_zone))->format("Y-m-d H:i:s");
        }
        array_multisort($online, SORT_DESC, $unread, SORT_DESC, $id, SORT_DESC, $guest);
        return [$guest, array_sum($unread)];
    }

    /**
     * 统计游客访问
     * @param $guestId
     * @param $referer
     * @param int $type
     * @return bool
     */
    public function trace($guestId, $referer, $type = 1) {
        $date = date("Y-m-d");
        $url = parse_url($referer, PHP_URL_HOST) ?: "";
        $guestUser = GuestUser::find($guestId);
        if (!$guestUser) {
            // 未知对象无法统计
            return false;
        }
        $trace = GuestTrace::where(["user_id"=> $guestUser["user_id"], "guest_id"=> $guestId, "store_url"=> $url, "total_date"=> $date])->first();
        if (!$trace) {
            $platform = Store::where("store_url", $url)->value("platform");
            $trace = GuestTrace::create([
                "user_id"=> $guestUser["user_id"],
                "guest_id"=> $guestId,
                "store_url"=> $url,
                "platform"=> $platform ?: 0,
                "total_date"=> $date,
            ]);
        }
        // type 1访问 2咨询
        switch ((int)$type) {
            case 2:
                $trace->is_chat = 1;
                break;
            default:
                return true;
                break;
        }
        return $trace->save();
    }

    /**
     * 客服状态变化通知
     * @param $userId
     */
    public function customerNotify($userId) {
        // 客服在线状态
        $status = ApplicationContext::getContainer()->get(UserService::class)->getOnlineStatus($userId);
        $socketIO = ApplicationContext::getContainer()->get(SocketIO::class);
        $chat = $socketIO->of(Chat::NAMESPACE);
        // 游客ID
        $guestIds = SocketUserCollector::getOnlineGuestIds($userId);
        foreach ($guestIds as $guestId) {
            $chat->to("2_" . $guestId)->emit("online", ["customer_service_status"=> $status]);
        }
    }
}