<?php
declare(strict_types=1);
namespace App\Controller\v1;
use App\Amqp\Producer\ShoplineProducer;
use App\Constants\ErrorCode;
use App\Model\Order;
use App\Model\Store;
use App\Service\EasyParcel\EasyParcelService;
use App\Service\Order\OrderService;
use App\Service\Store\StoreService;
use Hyperf\Amqp\Producer;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\Di\Annotation\Inject;
use App\Controller\v1\AbstractController;
use Hyperf\Logger\LoggerFactory;
use Qbhy\HyperfAuth\Annotation\Auth;
use Qbhy\HyperfAuth\AuthManager;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\JwtAuthMiddleware;
use App\Annotation\NotAuth;
/**
 * @Controller()
 * @NotAuth
 * Class OrderController
 * @package App\Controller\v1
 */
class OrderController extends AbstractController
{

    /**
     * @Inject()
     * @var LoggerFactory
     */
    protected $logger;

    /**
     * @Inject()
     * @var OrderService
     */
    protected $orderService;

    /**
     * @Inject()
     * @var EasyParcelService
     */
    protected $easyParcel;

    /**
     * @Inject()
     * @var StoreService
     */
    protected $store;

    /**
     * @Inject
     * @var Producer
     */
    protected $producer;

    /**
     * 订单通知回调
     * @RequestMapping(path="notify", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function notify(RequestInterface $request, ResponseInterface $response)
    {
        try {
            echo "hook = \r\n";
            $this->orderService->pushQueue($request->post());
            return $response->json(['code' => 200, 'msg' => 'ok']);
        }catch (\Exception $e){
            return $response->json(['code' => 200, 'msg' => $e->getMessage()]);
        }catch (\Throwable $e){
            //. 这里的需要记录日志
            $this->logger->get('order_callback_error','order_callback_error')
                ->error($e->getMessage());
            return $response->json(['code' => 200, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * easyParcel webhook
     * shipment/create
     * @RequestMapping(path="hook", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function easyParcel(RequestInterface $request, ResponseInterface $response)
    {
        try {
            echo "easy hook = \r\n";
            $this->easyParcel->webhook($request->post());
            return $response->json(['code' => 200, 'msg' => 'ok']);
        }catch (\Exception $e){
            return $response->json(['code' => 200, 'msg' => $e->getMessage()]);
        }catch (\Throwable $e){
            //. 这里的需要记录日志
            $this->logger->get('easy_parcel_callback_error','easy_parcel_callback_error')
                ->error($e->getMessage());
            return $response->json(['code' => 200, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * Test Connect
     * @RequestMapping(path="connect", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function checkConnect(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $post = $request->post();
            if (!$post){
                throw new \Exception('Params error');
            }
            $result = $this->easyParcel->testConnect($post['api']);
            if (!$result){
                throw new \Exception('Connect Fail!');
            }
            return $response->json(['code' => 200,'msg' => 'Connect Success!', 'data' => $result]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取easyparcel service
     * @RequestMapping(path="get_service", methods="get")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getService(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $result = $this->easyParcel->getServiceList();
            return $response->json(['code' => 200,'msg' => 'ok', 'data' => $result]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 保存配置
     * @RequestMapping(path="save", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function saveStore(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $params = $request->post();
            #. 先检查是否能链接
            $result = $this->easyParcel->testConnect($params['easy_api']);
            if (!$result){
                throw new \Exception('Connect EasyParcel Fail!');
            }
            $this->store->saveStore($params);
            return $response->json(['code' => 200,'msg' => 'ok']);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 推送列表
     * @RequestMapping(path="push_list", methods="get")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function pushList(RequestInterface $request, ResponseInterface $response)
    {
        try {
            if (!$params = $request->all()){
                throw new \Exception('参数错误');
            }
            if (!$params['handle']){
                throw new \Exception('handle错误');
            }
            $result = $this->easyParcel->getPushLog((string)$params['handle'], (int)$params['limit'], (int)$params['page']);
            return $response->json(['code' => 200,'msg' => 'ok', 'count' => $result['count'], 'data' => $result['data']]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 重新推送
     * @RequestMapping(path="re_push", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function rePush(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $result = true;
            $params = $request->post();
            if (!isset($params['handle']) && !$params['handle']){
                throw new \Exception('未找到handle');
            }
            $store = Store::where(['store_name' => $params['handle']])->first();
            if (!$store){
                throw new \Exception('Store Error');
            }
            $store->shopline_id;
            $order = Order::where(['store_id' => $store->shopline_id, 'is_exec' => 1])->get();
            if (!$order){
                return $response->json(['code' => 200,'msg' => 'ok', 'data' => $result]);
            }
            foreach ($order->toArray() as $v){
                $this->producer->produce(new ShoplineProducer($v));
            }
            return $response->json(['code' => 200,'msg' => 'ok']);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }
    /**
     * 手动
     * @RequestMapping(path="hans", methods="get")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function hans(RequestInterface $request, ResponseInterface $response)
    {
        $result = true;
        $params = $request->all();
        $store = Store::where(['store_name' => $params['handle']])->first();
        if (!$store){
            throw new \Exception('Store nou found');
        }
        $order = Order::where(['store_id' => $store->shopline_id, 'is_exec' => 1])->get();
        if (!$order){
            return $response->json(['code' => 200,'msg' => 'ok', 'data' => $result]);
        }
        foreach ($order->toArray() as $v){
            $this->producer->produce(new ShoplineProducer($v));
        }
        return $response->json(['code' => 200,'msg' => 'ok']);
    }
}
