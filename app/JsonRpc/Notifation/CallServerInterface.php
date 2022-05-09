<?php

namespace App\JsonRpc\Notifation;

/**
 * Interface CallServerInterface
 * @package App\JsonRpc\Notifation
 */
interface CallServerInterface
{
    public function SendMsg(array $params): array;
}
