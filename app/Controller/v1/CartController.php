<?php
declare(strict_types=1);
namespace App\Controller\v1;
use App\Constants\ErrorCode;
use App\Service\Cart\CartService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\Di\Annotation\Inject;
use App\Controller\v1\AbstractController;
use phpDocumentor\Reflection\File;

/**
 * Class CardController
 * @package App\Controller\v1
 */
class CartController extends AbstractController
{
    /**
     * @Inject
     * @var CartService
     */
    protected $cart;
    /**
     * sm卡列表
     * @RequestMapping(path="signin", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $params = $request->all();
            $result = $this->cart->getList($params);
            return $response->json(['code' => 200, 'msg' => 'ok', 'data'=> $result]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * sm卡充值
     * @RequestMapping(path="signin", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function recharge(RequestInterface $request, ResponseInterface $response)
    {
        try {
            if (!$request->isMethod('post')){
                throw new \Exception('请求错误');
            }
            $params = $request->post();
            $result = $this->cart->rechargeCard($params);
            return $response->json(['code' => 200, 'msg' => 'ok', 'data'=> $result]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 绑定sm卡
     * @RequestMapping(path="signin", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function bind(RequestInterface $request, ResponseInterface $response)
    {
        try {
            if (!$file = $request->file('file') instanceof File){
                throw new \Exception('请求错误');
            }
            $result = $this->cart->bindCard($request->file('file'));
            return $response->json(['code' => 200, 'msg' => 'ok', 'data'=> $result]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 更新sm卡套餐
     * @RequestMapping(path="signin", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function update(RequestInterface $request, ResponseInterface $response)
    {
        try {
            if (!$request->isMethod('post')){
                throw new \Exception('请求错误');
            }
            $result = $this->cart->channgedPackages($request->post());
            return $response->json(['code' => 200, 'msg' => 'ok', 'data'=> $result]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 查询sm卡话费
     * @RequestMapping(path="query", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function query(RequestInterface $request, ResponseInterface $response)
    {
        try {
            if (!$request->isMethod('post')){
                throw new \Exception('请求错误');
            }
            $result = $this->cart->queryTelephone($request->post());
            return $response->json(['code' => 200, 'msg' => 'ok', 'data'=> $result]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 更改资费套餐
     * @RequestMapping(path="up-status", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function upStatus(RequestInterface $request, ResponseInterface $response)
    {
        try {
            if (!$request->isMethod('post')){
                throw new \Exception('请求错误');
            }
            $this->cart->upStatus();
            return $response->json(['code' => 200, 'msg' => 'ok']);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }

    public function show(RequestInterface $request, ResponseInterface $response)
    {
        try {

        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }
}
