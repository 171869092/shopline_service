<?php


namespace App\Event;

/**
 * 游客动态变化事件
 * Class GuestActionEvent
 * @package App\Event
 */
class GuestActionEvent
{
    /**
     * 游客访问事件
     */
    const ACCESS_EVENT = 1;
    /**
     * 游客在线事件
     */
    const ONLINE_EVENT = 2;
    /**
     * 游客离线事件
     */
    const OFFLINE_EVENT = 3;
    /**
     * 游客咨询事件
     */
    const CONSULT_EVENT = 4;
    /**
     * 游客咨询（人工）事件
     */
    const WORKMAN_EVENT = 5;
    /**
     * 游客咨询（机器人）事件
     */
    const ROBOT_EVENT = 6;
    /**
     * 用户已读游客信息事件
     */
    const USER_READ_EVENT = 7;
    /**
     * @var int 触发的事件类型
     */
    public $target;
    /**
     * @var int 游客ID
     */
    public $guestId;

    /**
     * GuestActionEvent constructor.
     * @param int $guestId
     * @param int $event GuestActionEvent::ACCESS_EVENT,GuestActionEvent::ONLINE_EVENT,
     * GuestActionEvent::OFFLINE_EVENT,GuestActionEvent::CONSULT_EVENT,
     * GuestActionEvent::WORKMAN_EVENT,GuestActionEvent::ROBOT_EVENT,
     * GuestActionEvent::USER_READ_EVENT,
     */
    public function __construct($guestId, $event)
    {
        $this->guestId = $guestId;
        $this->target = $event;
    }
}