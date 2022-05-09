<?php

namespace App\JsonRpc\Notifation;


/**
 * Interface NotifationServerBInterface
 * @package App\JsonRpc\Notifation
 */
interface NotifationServerBInterface
{
    public function createNotifation(int $serverId, array $params): bool;
}
