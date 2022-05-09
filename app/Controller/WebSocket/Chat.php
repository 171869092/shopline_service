<?php

declare(strict_types=1);

namespace App\Controller\WebSocket;

use App\Collector\SocketUserCollector;
use App\Event\GuestActionEvent;
use App\Model\Dialogue;
use App\Model\GuestUser;
use App\Model\User;
use App\Service\GuestManager\GuestInfoService;
use App\Service\User\UserService;
use Hyperf\SocketIOServer\Annotation\Event;
use Hyperf\SocketIOServer\Annotation\SocketIONamespace;
use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\Socket;
use Hyperf\Utils\ApplicationContext;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Coroutine;

/**
 * 聊天
 * @SocketIONamespace("/")
 */
class Chat extends BaseNamespace
{
    const NAMESPACE = "/";
    /**
     * 连接
     * @Event("connect")
     * @param Socket $socket
     */
    public function connect(Socket $socket) {
        $user = SocketUserCollector::getUser($socket->getFd(), ["id", "is_not_touch", "symbol", "user_id"]);
        if ($user && $user["symbol"] == "2") {
            $this->guestEvent($user["id"], GuestActionEvent::ONLINE_EVENT);
            // 客服状态
            $status = ApplicationContext::getContainer()->get(UserService::class)->getOnlineStatus($user["user_id"]);
            $socket->emit("online", ["customer_service_status"=> $status]);
        } else {
            // 客服在线通知
            ApplicationContext::getContainer()->get(GuestInfoService::class)->customerNotify($user["id"]);
        }
        // 连接成功后需要发送聊天处理状况
        $socket->emit("touch", ["is_not_touch"=> $user["is_not_touch"] ?? "0"]);
        $socket->join($user["symbol"] . "_" . $user["id"]);
    }

    /**
     * 断开连接
     * @Event("disconnect")
     * @param Socket $socket
     */
    public function disconnect(Socket $socket) {
        // 如果是正在处理的连接断开，则需要设置其他在线用户可聊天
        $user = SocketUserCollector::getUser($socket->getFd(), ["id", "symbol", "is_not_touch"]);
        if ($user && $user["symbol"] == "2") {
            $this->guestEvent($user["id"], GuestActionEvent::OFFLINE_EVENT);
        }
        if ($user && $user["is_not_touch"] == '0') {
            $fds = SocketUserCollector::getUserFd($user["symbol"], $user["id"]);
            foreach ($fds as $fd) {
                if ($fd != $socket->getFd()) {
                    $status = ["is_not_touch"=> "0"];
                    SocketUserCollector::modifyUser($fd, $status);
                    $this->to($this->sidProvider->getSid((int)$fd))->emit("touch", $status);
                    break;
                }

            }
        }
        SocketUserCollector::clear($socket->getFd());
        // 客服离线通知
        if ($user["symbol"] == "1") {
            ApplicationContext::getContainer()->get(GuestInfoService::class)->customerNotify($user["id"]);
        }
    }

    /**
     * 在线交谈
     * @Event("touch")
     * @param Socket $socket
     */
    public function touch(Socket $socket)
    {
        $user = SocketUserCollector::getUser($socket->getFd(), ["id", "symbol"]);
        $socket->emit("touch", ["is_not_touch"=> "0"]);
        $socket->to($user["symbol"] . "_" . $user["id"])->emit("touch", ["is_not_touch"=> "1"]);
    }

    /**
     * 聊天
     * @Event("mutual")
     * @param Socket $socket
     * @param array $data
     */
    public function mutual(Socket $socket, $data)
    {
        $user = SocketUserCollector::getUser($socket->getFd());
        // symbol等于1是登录的用户，其他的是游客
        if (isset($user["symbol"]) && $user["symbol"] == '1') {
            // 登录用户
            if (!empty($data["guest_id"])) {
                $guest = GuestUser::find($data["guest_id"]);
                if ($guest) {
                    // 聊天消息
                    $dialogue = Dialogue::create([
                        "user_id"=> $user["id"],
                        "guest_id"=> $data["guest_id"],
                        "target"=> 1,
                        "user_name"=> $user["user_name"],
                        "guest_name"=> $guest["guest_name"],
                        "type"=> $data["type"] ?? 1,
                        "content"=> $data["content"] ?? "",
                        "attachment"=> !empty($data["attachment"]) ? $data["attachment"] : null,
                        "user_read"=> 1,
                    ]);
                    // 发送消息
                    $this->send($socket, [["symbol"=> 1, "id"=> $user["id"]], ["symbol"=> 2, "id"=> $data["guest_id"]]], array_merge($dialogue->toArray(), ["user_avatar"=> $user["user_avatar"]]));
                    // 发送成功
                    $socket->emit('sent', $data);
                }
            }
        } else {
            $this->guestEvent($user["id"], GuestActionEvent::CONSULT_EVENT);
            // 游客，是否已经有标识，没有则发送问卷获取用户邮箱和用户名
            $guest = GuestUser::find($user["id"]);
            if (!$guest) {
                // 客服删除的用户发信息
                $insert = ["id", "is_contact","user_id", "guest_name", "email", "phone", "note", "referer_title", "referer", "location", "user_agent", "browser", "device", "ip", "time_zone"];
                $addData = [];
                foreach ($insert as $key) {
                    $addData[$key] = $user[$key] ?? null;
                }
                GuestUser::insert($addData);
            }
            if (!$user['is_contact'] && (!isset($data["type"]) || $data["type"] != 2)) {
                GuestUser::where("id", $user["id"])->update(["is_contact"=> 1]);
                SocketUserCollector::modifyUser($socket->getFd(), ["is_contact"=> 1]);
                $time_zone = !empty($user["time_zone"]) ? $user["time_zone"] : "Asia/Shanghai";
                $notice = Dialogue::create($this->collectionUser($user));
                $notice->create_time->setTimezone(new \DateTimeZone($time_zone));
                $notice->update_time->setTimezone(new \DateTimeZone($time_zone));
                // 发送问卷
                $this->to("2_" . $user["id"])->emit("mutual", $notice->toArray());
                $this->guestEvent($user["id"], GuestActionEvent::ROBOT_EVENT);
            }
            if ($data["type"] == 2) {
                $extra = $data["extra"] ?? [];
                // 邮箱和用户名处理
                GuestUser::where("id", $user["id"])->update(["guest_name"=> $extra["guest_name"] ?? "", "email"=> $extra["email"] ?? "", "is_contact"=> 1]);
                Dialogue::where("guest_id", $user["id"])->update(["guest_name"=> $extra["guest_name"] ?? ""]);
                // 记录用户填写信息
                if (!empty($data["dialogue_id"])) Dialogue::where("id", $data["dialogue_id"])->update(["extra"=> json_encode(["guest_name"=> $extra["guest_name"] ?? "", "email"=> $extra["email"] ?? ""])]);
            } else {
                // 聊天消息
                $user_name = User::where("id", $user["user_id"])->value("user_name");
                $dialogue = Dialogue::create([
                    "user_id"=> $user["user_id"],
                    "guest_id"=> $user["id"],
                    "target"=> 2,
                    "user_name"=> $user_name,
                    "guest_name"=> $user["guest_name"],
                    "type"=> $data["type"] ?? 1,
                    "content"=> $data["content"] ?? "",
                    "attachment"=> !empty($data["attachment"]) ? $data["attachment"] : null,
                    "guest_read"=> 1,
                ]);
                $user_avatar = ApplicationContext::getContainer()->get(UserService::class)->getUserAvatar($user["user_id"]);
                // 发送消息
                $this->send($socket, [["symbol"=> 1, "id"=> $user["user_id"]], ["symbol"=> 2, "id"=> $user["id"]]], array_merge($dialogue->toArray(), ["user_avatar"=> $user_avatar]));
                $this->guestEvent($user["id"], GuestActionEvent::WORKMAN_EVENT);
            }
            // 发送成功
            $socket->emit('sent', $data);
        }
    }

    /**
     * 已读信息
     * @Event("read")
     * @param Socket $socket
     * @param $data
     */
    public function read(Socket $socket, $data) {
        $user = SocketUserCollector::getUser($socket->getFd(), ["id", "symbol"]);
        if ($user["symbol"] == '1') {
            // 已读
            Dialogue::where(["user_id"=> $user["id"], "guest_id"=> $data["guest_id"], "user_read"=> 0])->update(["user_read"=> 1]);
            // 信息列表已读
            $this->guestEvent($data["guest_id"], GuestActionEvent::USER_READ_EVENT);
        } else {
            Dialogue::where(["guest_id"=> $user["id"], "guest_read"=> 0])->update(["guest_read"=> 1]);
        }
        // 发送成功
        $socket->emit('sent', $data);
    }

    /**
     * 聊天记录
     * @Event("record")
     * @param Socket $socket
     * @param $data
     */
    public function record(Socket $socket, $data) {
        $user = SocketUserCollector::getUser($socket->getFd(), ["id", "symbol", "user_id", "time_zone"]);
        if ($user["symbol"] == '1') {
            $user_id = $user["id"];
            if (empty($data["guest_id"])) {
                return;
            }
            $guest_id = $data["guest_id"];
            $readField = "user_read";
        } else {
            $user_id = $user["user_id"];
            $guest_id = $user["id"];
            $readField = "guest_read";
        }
        $list = Dialogue::select("*")
            ->selectSub(User::select("avatar")->whereRaw("user.id = dialogue.user_id"), "user_avatar")
            ->where(["user_id"=> $user_id, "guest_id"=> $guest_id])
            ->when($data["dialogue_id"] ?? "", function ($query, $dialogue_id) {
                return $query->where("id", "<", $dialogue_id);
            })
            ->when($user["symbol"] == '1', function ($query) {
                return $query->where("target", "!=", 3);
            })
            ->orderBy("id", "desc")
            // ->limit(50)
            ->get()
            ->toArray();
        $time_zone = !empty($user["time_zone"]) ? $user["time_zone"] : "Asia/Shanghai";
        foreach ($list as &$item) {
            $item["create_time"] = date_create($item["create_time"])->setTimezone(new \DateTimeZone($time_zone))->format("Y-m-d H:i:s");
            $item["update_time"] = date_create($item["update_time"])->setTimezone(new \DateTimeZone($time_zone))->format("Y-m-d H:i:s");
        }
        $socket->emit("record", ["data"=> $list, "is_end"=> count($list) < 50, "is_first_page"=> empty($data["dialogue_id"])]);
        // 已读
        Dialogue::where(["user_id"=> $user_id, "guest_id"=> $guest_id, $readField=> 0])->update([$readField=> 1]);
        if ($user["symbol"] == '1') {
            // 信息列表已读
            $this->guestEvent($user["id"], GuestActionEvent::USER_READ_EVENT);
        }
    }

    /**
     * 获取游客列表
     * @Event("visitor")
     * @param Socket $socket
     * @param $data
     */
    public function visitor(Socket $socket, $data) {
        $user = SocketUserCollector::getUser($socket->getFd(), ["id", "symbol"]);
        if ($user["symbol"] == '1') {
            [$guest, $unread_count] = ApplicationContext::getContainer()->get(GuestInfoService::class)->catalog($user["id"]);
            $socket->emit("visitor", compact("guest", "unread_count"));
        }
    }

    /**
     * 游客事件
     * @param $guestId
     * @param $event
     */
    protected function guestEvent($guestId, $event) {
        Coroutine::create(function () use ($guestId, $event) {
            $eventDispatcher = ApplicationContext::getContainer()->get(EventDispatcherInterface::class);
            $eventDispatcher->dispatch(new GuestActionEvent($guestId, $event));
        });
    }

    /**
     * 针对用户发送信息
     * @param Socket $socket
     * @param $userList
     * @param $message
     */
    protected function send(Socket $socket, $userList, $message) {
        foreach ($userList as $user) {
            if ($user["symbol"] == 1) {
                $time_zone = User::where("id", $user["id"])->value("time_zone");
            } else {
                $time_zone = GuestUser::where("id", $user["id"])->value("time_zone");
            }
            $time_zone = $time_zone ?: "Asia/Shanghai";
            if (!empty($message["create_time"])) {
                $message["create_time"] = date_create($message["create_time"])->setTimezone(new \DateTimeZone($time_zone))->format("Y-m-d H:i:s");
            }
            if (!empty($message["update_time"])) {
                $message["update_time"] = date_create($message["update_time"])->setTimezone(new \DateTimeZone($time_zone))->format("Y-m-d H:i:s");
            }
            $socket->to($user["symbol"] . "_" . $user["id"])->emit("mutual", $message);
        }
    }

    /**
     * @param $user
     * @return array
     */
    protected function collectionUser($user) {
        return [
            "user_id"=> $user["user_id"],
            "guest_id"=> $user["id"],
            "target"=> 3,
            "user_name"=> "机器人",
            "guest_name"=> $user["guest_name"] ?? "",
            "type"=> 2,
            "content"=> "",
            "attachment"=> null,
            "extra"=> [
                "guest_name"=> "",
                "email"=> "",
            ],
            "guest_read"=> 0,
            "user_read"=> 1,
            "create_time"=> date("Y-m-d H:i:s"),
            "update_time"=> date("Y-m-d H:i:s"),
        ];
    }
}