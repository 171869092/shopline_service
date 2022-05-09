<?php

namespace App\JsonRpc\After;

interface AfterServerInterface
{
    /**
     * 创建售后
     * @param array $post
     * @return bool
     */
    public function afterSave(array $post): bool;

    /**
     * 回复售后
     * @param $serverId
     * @param $afterId
     * @param $data
     * @return bool
     */
    public function reply($serverId, $afterId, $data) :bool;

    /**
     * 已读售后信息
     * @param $serverId
     * @param $afterId
     * @return bool
     */
    public function isRead($serverId, $afterId) :bool;
}
