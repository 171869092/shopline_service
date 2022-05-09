<?php

namespace App\JsonRpc\After;


/**
 * Interface FbaliAfterServerInterface
 * @package App\JsonRpc\After
 */
interface FbaliAfterServerInterface
{
    /**
     * 回复售后
     * @param array $data
     * @return int
     * @throws \Exception
     */
    public function reply(array $data);

    /**
     * 已读售后消息
     * @param $afterId
     * @param $type
     * @return bool
     */
    public function isRead($afterId, $type = 1) :bool;

    /**
     * 获取售后信息
     * @param $afterId
     * @return array
     */
    public function getAfterSales($afterId) :array;
}