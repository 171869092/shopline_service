<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Collector\SocketUserCollector;
use App\Model\GuestUser;
use App\Model\User;
use Closure;
use FastRoute\Dispatcher;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qbhy\HyperfAuth\AuthManager;
use Swoole\Http\Request;

class WebSocketAuthMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Dispatched $dispatched */
        $dispatched = $request->getAttribute(Dispatched::class);
        // 伪代码，通过 isAuth 方法拦截握手请求并实现权限检查
        if ($dispatched instanceof Dispatched && $this->shouldHandle($dispatched) && !$this->isAuth($request)) {
            return $this->container->get(\Hyperf\HttpServer\Contract\ResponseInterface::class)->raw('Forbidden')->withStatus(403);
        }


        return $handler->handle($request);
    }

    /**
     * 连接鉴权
     * @param ServerRequestInterface $request
     * @return bool
     */
    private function isAuth(ServerRequestInterface $request) :bool {
        $param = $request->getQueryParams();
        /** @var Request $serverRequest */
        $serverRequest = $request->getSwooleRequest();
        if (!empty($param["symbol"])) {
            // 1:登录的客服连接
            switch ($param["symbol"]) {
                case "1":
                    $authManager = ApplicationContext::getContainer()->get(AuthManager::class)->guard("jwt");
                    break;
                default:
                    // 游客
                    $authManager = ApplicationContext::getContainer()->get(AuthManager::class)->guard("jwt-guest");
                    $param["symbol"] = '2';
                    break;
            }
            if (!$authManager->check()) {
                return false;
            }
            /** @var User|GuestUser $user */
            $user = $authManager->user();
            $userInfo = $user->toArray();
            // 判断是否有用户在线
            $userInfo["is_not_touch"] = SocketUserCollector::hasUser(1, $userInfo["id"]) ? "1" : "0";
            if (!empty($userInfo)) {
                $userInfo["symbol"] = $param["symbol"];
                $userInfo["online_time"] = time();
                SocketUserCollector::bindUser($serverRequest->fd, $userInfo);
                return true;
            }
        }
        return false;
    }

    /**
     * @param Dispatched $dispatched
     * @return bool
     */
    protected function shouldHandle(Dispatched $dispatched): bool
    {
        return $dispatched->status === Dispatcher::FOUND && ! $dispatched->handler->callback instanceof Closure;
    }
}