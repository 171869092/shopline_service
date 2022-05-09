<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */


return [
    'consumers' => value(function () {
        $consumers = [];
        // 这里示例自动创建代理消费者类的配置形式，顾存在 name 和 service 两个配置项，这里的做法不是唯一的，仅说明可以通过 PHP 代码来生成配置
        // 下面的 FooServiceInterface 和 BarServiceInterface 仅示例多服务，并不是在文档示例中真实存在的
        $services_c = [
//            'FbaliUserServer' => \App\JsonRpc\User\FbaliUserServerInterface::class,
//            'FbaliAfterServer' => \App\JsonRpc\After\FbaliAfterServerInterface::class,
//            'NotifationCServer' => \App\JsonRpc\Notifation\NotifationCServerInterface::class
        ];
        foreach ($services_c as $name => $interface) {
            $consumers[] = [
                'name' => $name,
                'service' => $interface,
                'nodes' => [
                    // Provide the host and port of the service provider.
                    ['host' => env("HYC_SERVICE_RPC_HOST", "20.0.0.230"), 'port' => (int)env("HYC_SERVICE_RPC_PORT",9504)]
                ],
                'protocol' => 'jsonrpc-tcp-length-check',
                // 配置项，会影响到 Packer 和 Transporter
                'options' => [
                    'connect_timeout' => 10.0,
                    'recv_timeout' => 10.0,
                    'settings' => [
                        // 根据协议不同，区分配置
                        // 'open_eof_split' => true,
                        // 'package_eof' => "\r\n",
                        'open_length_check' => true,
                        'package_length_type' => 'N',
                        'package_length_offset' => 0,
                        'package_body_offset' => 4,
                        'package_max_length' => 1024 * 1024 * 2,
                    ],
                    // 重试次数，默认值为 2，收包超时不进行重试。暂只支持 JsonRpcPoolTransporter
                    'retry_count' => 2,
                    // 重试间隔，毫秒
                    'retry_interval' => 100,
                    // 当使用 JsonRpcPoolTransporter 时会用到以下配置
                    'pool' => [
                        'min_connections' => 1,
                        'max_connections' => 32,
                        'connect_timeout' => 10.0,
                        'wait_timeout' => 3.0,
                        'heartbeat' => -1,
                        'max_idle_time' => 60.0,
                    ],
                ],
            ];
        }

        $services_b = [
//            'TechUserServer' => \App\JsonRpc\User\TechUserServerInterface::class,
//            'AfterServer' => \App\JsonRpc\After\AfterServerInterface::class,
//            'NotifationServerB' => \App\JsonRpc\Notifation\NotifationServerBInterface::class
        ];
        foreach ($services_b as $name_b => $interface_b) {
            $consumers[] = [
                'name' => $name_b,
                'service' => $interface_b,
                'nodes' => [
                    // Provide the host and port of the service provider. HYC_SERVICE_RPC_PORT
                    ['host' => env("HYB_SERVICE_RPC_HOST", "10.0.0.174"), 'port' => (int)env("HYB_SERVICE_RPC_PORT",9503)]
                ],
                'protocol' => 'jsonrpc-tcp-length-check',
                // 配置项，会影响到 Packer 和 Transporter
                'options' => [
                    'connect_timeout' => 10.0,
                    'recv_timeout' => 10.0,
                    'settings' => [
                        // 根据协议不同，区分配置
                        // 'open_eof_split' => true,
                        // 'package_eof' => "\r\n",
                        'open_length_check' => true,
                        'package_length_type' => 'N',
                        'package_length_offset' => 0,
                        'package_body_offset' => 4,
                        'package_max_length' => 1024 * 1024 * 2,
                    ],
                    // 重试次数，默认值为 2，收包超时不进行重试。暂只支持 JsonRpcPoolTransporter
                    'retry_count' => 2,
                    // 重试间隔，毫秒
                    'retry_interval' => 100,
                    // 当使用 JsonRpcPoolTransporter 时会用到以下配置
                    'pool' => [
                        'min_connections' => 1,
                        'max_connections' => 32,
                        'connect_timeout' => 10.0,
                        'wait_timeout' => 3.0,
                        'heartbeat' => -1,
                        'max_idle_time' => 60.0,
                    ],
                ],
            ];
        }
        return $consumers;
    }),
];
