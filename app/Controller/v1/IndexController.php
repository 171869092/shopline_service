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
namespace App\Controller\v1;
use App\Common\Request;
use App\Constants\ErrorCode;
use App\Model\Store;
use App\Service\Store\StoreService;
use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Snowflake\IdGeneratorInterface;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;
use Qbhy\HyperfAuth\Annotation\Auth;
use Qbhy\HyperfAuth\AuthManager;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\JwtAuthMiddleware;
use App\Annotation\NotAuth;

/**
 * Class IndexController
 * @NotAuth
 * @Controller()
 * @package App\Controller
 */
class IndexController extends AbstractController
{
    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var StoreService
     */
    protected $storeService;

    /**
     * @Inject
     * @var Request
     */
    protected $resServer;

    /**
     * install
     * @RequestMapping(path="install", methods="get")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function install(RequestInterface $request, ResponseInterface $response)
    {
        try {
            if (!$get = $request->all()){
                throw new \Exception('Params error');
            }

            $token = '';
            return $response->json(['code' => 200, 'msg' => 'ok', 'data' => $token]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * @RequestMapping(path="call", methods="get")
     */
    public function live(RequestInterface $request, ResponseInterface $response)
    {
        /**
         * 1; 先检测店铺是否在系统注册
         * 2; 存在就直接进入后续逻辑
         * 3; 如果不存在就进入授权流程
         * @: 授权流程
         * 1> 拼接授权地址,重定向到授权页面,用户点击授权
         * 2> 确认授权后携带code到callbck地址
         * 3> 验证sign,携带code 创建token,并保存
         */
        try {
            $params = $request->all();
            if (!isset($params['handle']) || !isset($params['sign']) || !isset($params['code'])) {
                throw new \Exception('参数错误,请重新安装');
            }
            $url = 'https://'.$params['handle'].'.myshopline.com/admin/oauth/token/create';
            $token = $this->resServer->authToken($url, [
                'appkey' => $params['appkey'],
                'sign' => $params['sign'],
                'timestamp' => $params['timestamp'],
                'code' => $params['code']
                ]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
//        $result = $this->storeService->ex($request->all());
        return $response->json(['code' => 200,'msg' => 'ok', 'data' => $token]);
    }

    /**
     * @RequestMapping(path="index", methods="get")
     */
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $params = $request->all();
            if (!isset($params['handle']) || !isset($params['sign'])){
                throw new \Exception('params error');
            }
            $store = Store::query()->where(['store_name' => $params['handle']])->first();
            #. 未找到就进入授权安装流程
            if (!$store){
                $link = $this->resServer->oauth($params['handle']);
                if (!$link){
                    throw new \Exception('拼接授权地址失败;');
                }
                return $response->json(['code' => 200,'msg' => 'link', 'data' => $link]);
            }
            /**@var Store $store*/
            $data = $this->auth->login($store);
            return $response->json(['code' => 200,'msg' => 'ok', 'token' => $data]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }
}
