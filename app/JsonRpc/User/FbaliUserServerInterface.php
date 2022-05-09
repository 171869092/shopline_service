<?php

namespace App\JsonRpc\User;

/**
 * Interface FbaliUserServerInterface
 * @package App\JsonRpc\User
 */
interface FbaliUserServerInterface
{
    /**
     * 获取C端用户信息
     * @param $userId
     * @return array
     */
    public function info($userId): array;
}