<?php

namespace App\Service\AwsQueue;

interface ProducerInterface
{
    /**
     * 发送aws队列,
     * @param array $data
     * @param int $delay
     * @return mixed|null
     */
    public function run(array $data, int $delay = 0): array;

    /**
     * 生成sku--此处用来为队列Tag生成随机字符串
     * @return string
     */
    public function GenerateSku(bool $alpha = true, bool $nums = true, bool $usetime = false, string $string = '', int $length = 120): string;

    /**
     * product
     * @param array $data
     * @param int $delay
     * @return array
     */
    public function commonrun(array $data, int $delay = 0): array;

    /**
     * @return array|bool
     */
    public function receive();
}
