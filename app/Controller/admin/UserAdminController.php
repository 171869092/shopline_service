<?php
declare(strict_types=1);

namespace App\Controller\admin;

use App\Constants\ErrorCode;
use App\Model\UserAdmin;
use App\Service\UserAdmin\UserAdminService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\JwtAuthMiddleware;
use App\Annotation\NotAuth;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Context;
use Qbhy\HyperfAuth\Annotation\Auth;
use Qbhy\HyperfAuth\AuthManager;

/**
 * Class UserAdminController
 * @NotAuth
 * @Controller()
 * @package App\Controller\admin
 */
class UserAdminController extends AbstractController
{
    /**
     * @Inject()
     * @var UserAdminService
     */
    protected $user;
    /**
     * @RequestMapping(path="signin", methods="get,post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function signin(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $post = $request->post();
            if (!$post){
                throw new \Exception('参数错误');
            }
            /**@var UserAdmin $user*/
            if (!$user = $this->user->getUser((int)$post['phone'], (string)$post['password'])){
                throw new \Exception('未找到该用户');
            }
            $auth = $this->auth
                ->guard('jwt-admin')
                ->login($user);
            if (!$auth) throw new \Exception('登陆失败');
            return $response->json(['code' => 200, 'msg' => 'ok', 'data'=> $auth]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * @RequestMapping(path="signout", methods="get,post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function signout(RequestInterface $request, ResponseInterface $response)
    {
//        $this->auth->guard('jwt-admin')->logout();
        return $response->json(['code' => 200, 'msg' => 'ok']);
    }
}
