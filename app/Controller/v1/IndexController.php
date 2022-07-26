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
use App\Model\Country;
use App\Model\Service;
use App\Model\Store;
use App\Model\Token;
use App\Service\EasyParcel\EasyParcelService;
use App\Service\Order\OrderService;
use App\Service\Store\StoreService;
use Hyperf\Contract\ConfigInterface;
use Hyperf\DbConnection\Db;
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
     * @Inject
     * @var OrderService
     */
    protected $orderServer;

    /**
     * @Inject
     * @var EasyParcelService
     */
    protected $easyParcel;

    /**
     * 获取配置项
     * @RequestMapping(path="config", methods="get")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getConfig(RequestInterface $request, ResponseInterface $response)
    {
        try {
            if (!$get = $request->all()){
                throw new \Exception('Params error');
            }
            $result = $this->easyParcel->getConfig($get['handle']);

            return $response->json(['code' => 200, 'msg' => 'ok', 'data' => $result['data']]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 安装授权的地方
     * @RequestMapping(path="call", methods="get")
     */
    public function call(RequestInterface $request, ResponseInterface $response)
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
            if (Store::where(['store_name' => $params['handle']])->first()){
                throw new \Exception('已经安装过了');
            }
            $uri = 'https://'.$params['handle'].'.myshopline.com';
            $url = '/admin/oauth/token/create';
            $token = $this->resServer->authToken($uri, $url, [
                'appkey' => $params['appkey'],
                'sign' => $params['sign'],
                'timestamp' => $params['timestamp'],
                'code' => $params['code'],
                'handle' => $params['handle']
                ]);
            if (!$token) throw new \Exception('获取token失败,请联系开发人员');
            $token = json_decode($token, true);

            #. 获取店铺信息
            $storeUrl = '/admin/openapi/v20210901/merchants/shop.json';
            $resStore = $this->resServer->requestStore($uri, $storeUrl,$token['data']['accessToken']);
            echo "\r\n resStore = \r\n";
            if (!$resStore){
                throw new \Exception('获取店铺信息失败');
            }
            print_r($resStore);
            Db::beginTransaction();
            #. 保存token
            $sToken = Token::insert(['handle' => $params['handle'],'token' => $token['data']['accessToken'],'expire_time' =>$token['data']['expireTime'], 'scope' => $token['data']['scope'], 'update_time' => date('Y-m-d H:i:s')]);
            $store = Store::insert([
                'shopline_id' => $resStore['data']['id'],
                'biz_store_status' => $resStore['data']['biz_store_status'],
                'store_name' => $params['handle'],
                'language' => $resStore['data']['language'],
                'currency' => $resStore['data']['currency'],
                'domain' => $resStore['data']['domain'],
                'customer_email' => $resStore['data']['customer_email'],
                'created_at' => $resStore['data']['created_at'],
                'token' => $token['data']['accessToken'],
                'create_time' => date('Y-m-d H:i:s')
            ]);
            if (!$sToken || !$store){
                Db::rollBack();
                throw new \Exception('保存token失败');
            }
            Db::commit();
            #. 这里就需要等待用户进行配置然后触发订单操作
//            $push = $this->orderServer->pushQueue(['handle' => $params['handle'], 'token' => $token['data']['accessToken']]);
            $sto = Store::query()->where(['store_name' => $params['handle']])->first();
            /**@var Store $sto*/
            $data = $this->auth->login($sto);
            return $response->json(['code' => 200,'msg' => 'ok', 'token' => $data]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
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
                return $response->json(['code' => 200,'msg' => 'link', 'data' => str_replace('\\','',$link)]);
            }
            /**@var Store $store*/
            $data = $this->auth->login($store);
            return $response->json(['code' => 200,'msg' => 'ok', 'token' => $data]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * @RequestMapping(path="live", methods="get")
     */
    public function testLive(RequestInterface $request, ResponseInterface $response)
    {
//        $data = (new EasyParcelService())->mPSubmitOrderBulk();
        $uri = 'https://lives-will.myshopline.com/';
        $url = 'admin/openapi/v20210901/merchants/shop.json';
        $token = 'eyJhbGciOiJIUzUxMiJ9.eyJzZWxsZXJJZCI6IjIwMDA5Nzk2ODYiLCJkb21haW4iOiJodHRwczovL3NsLW9wZW4tdXMubXlzaG9wbGluZS5jb20iLCJpc3MiOiJ5c291bCIsImFwcEtleSI6ImU5ODc1NjRjODU2YzA3OGI0NGY5NzYyMjdlYTExOWRkM2M3OTA5NzkiLCJzdG9yZUlkIjoiMTY1MjE4NzA5NjgxMCIsImV4cCI6MTY1MzQ4MzI1MywidmVyc2lvbiI6IlYyIiwidGltZXN0YW1wIjoxNjUzNDQ3MjUzMTYxfQ.74ym_b7F_PgtBwe6ZPbyZCv5D_8G6MXk8wI3vxyNyN21GfKDB37vPadDzhBj1NkWAWtb3Q5VJum7d_l6POXh2g';
        #. 获取店铺信息
        $data = $this->resServer->requestStore($uri, $url,$token);
        echo "\r\n resStore = \r\n";
        print_r($data);
        return $response->json(['code' => 200,'msg' => 'ok', 'data' => $data]);
    }

    /**
     * 获取国家
     * @RequestMapping(path="get-country", methods="get")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function getCountry(RequestInterface $request, ResponseInterface $response)
    {
        $data =  Country::get();
//        foreach ($data as &$v){
//            $ser = Service::where(['country' => $v['code']])->get();
//            $v['services'] = $ser;
//        }
        return $response->json(['code' => 200,'msg' => 'ok', 'data' => $data]);
    }

    /**
     * 获取国家service id
     * @RequestMapping(path="get-country-info", methods="get")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function getCountryInfo(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $params = $request->all();
            if (!isset($params['code']) && empty($params['code'])){
                throw new \Exception('Country code not found');
            }
            $data = Service::where(['country' => $params['code']])->get();
            return $response->json(['code' => 200,'msg' => 'ok', 'data' => $data]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }
}
