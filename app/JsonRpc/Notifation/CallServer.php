<?php

declare(strict_types=1);

namespace App\JsonRpc\Notifation;

use App\Service\Notifation\NotifationService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\RpcServer\Annotation\RpcService;
use Psr\Container\ContainerInterface;

/**
 * @RpcService(name="CallServer", protocol="jsonrpc-tcp-length-check", server="socket-jsonrpc-tcp")
 */
class CallServer implements CallServerInterface
{
    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

    public function SendMsg(array $params) :array {
        $container = $this->container->get(NotifationService::class);
        return $container->SendMsg($params);
    }
}
