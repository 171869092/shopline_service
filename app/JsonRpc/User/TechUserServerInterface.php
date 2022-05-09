<?php

namespace App\JsonRpc\User;


/**
 * Interface TechUserServerInterface
 * @package App\JsonRpc\User
 */
interface TechUserServerInterface
{
    /**
     * 获取B端用户信息
     * @param $userId
     * @return array
     */
    public function info($userId): array;
}