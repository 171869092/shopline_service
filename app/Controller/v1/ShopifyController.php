<?php
declare(strict_types=1);
namespace App\Controller\v1;
use App\Constants\ErrorCode;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

/**
 * Class ShopifyController
 * @package App\Controller\v1
 */
class ShopifyController extends AbstractController
{
    public function register(RequestInterface $request, ResponseInterface $response)
    {
        try {
            return $response->json([]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }
}
