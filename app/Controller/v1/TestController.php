<?php
declare(strict_types=1);

namespace App\Controller\v1;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\JwtAuthMiddleware;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;
use Qbhy\HyperfAuth\AuthManager;
use App\Annotation\NotAuth;
/**
 * Class IndexController
 * @Controller()
 * @NotAuth
 * @package App\Controller
 * @Middleware(JwtAuthMiddleware::class)
 */
class TestController extends AbstractController
{
    /**
     * @Inject
     * @var AuthManager
     */
    protected $auth;

    /**
     * @RequestMapping(path="live", methods="get")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return array
     */
    public function live()
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();

        return [
            'method' => $method,
            'message' => "Hello {$user}.",
        ];
    }

    /**
     *
     * 使用 Auth 注解可以保证该方法必须通过某个 guard 的授权，支持同时传多个 guard，不传参数使用默认 guard
     * @RequestMapping(path="user", methods="get")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return string
     */
    public function user(RequestInterface $request, ResponseInterface $response)
    {
        $token = $request->all();
        $container = ApplicationContext::getContainer()->get(Redis::class);
//        $request->header('Authorization',$token);
        $user = $this->auth->guard('jwt')->user($token['token'] ?? '');
        print_r($user);
        return 'hello '.$user->getId();
    }
}
