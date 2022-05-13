<?php
declare(strict_types=1);
namespace App\Controller\v1;
use App\Constants\ErrorCode;
use App\Service\Order\OrderService;
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
     * 订单通知回调
     * @RequestMapping(path="notify", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function notify(RequestInterface $request, ResponseInterface $response)
    {
        try {
            echo "hook = \r\n";
            print_r($request->post());
            return $response->json(['code' => 200, 'msg' => 'ok']);
        }catch (\Exception $e){
            return $response->json(['code' => 200, 'msg' => $e->getMessage()]);
        }catch (\Throwable $e){
            //. 这里的需要记录日志
            $this->logger->get('order_callback_error','order_callback_error')
                ->error($e->getMessage());
        }
    }

    /**
     * 下单
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function unify(RequestInterface $request, ResponseInterface $response)
    {
        try {
            if (!$params = $request->post()){
                throw new \Exception('参数错误');
            }
            if (!isset($params['user_id']) && empty($params['user_id'])){
                throw new \Exception('用户id错误');
            }
            $result = $this->orderService->unifyOrder($params);
            return $response->json(['code' => 200, 'msg' => 'ok', 'data' => $result]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }
}
