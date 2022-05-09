<?php

namespace App\JsonRpc\Notifation;

/**
 * Interface NotifationCServerInterface
 * @package App\JsonRpc\Notifation
 */
interface NotifationCServerInterface
{
    public function createNotifation(array $params): bool;
}
