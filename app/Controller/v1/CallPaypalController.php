<?php
declare(strict_types=1);

namespace App\Controller\v1;
use App\Annotation\NotAuth;
use App\Service\Shopify\ShopifyFactory;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

/**
 * Class PaypalController
 * @Controller()
 * @NotAuth
 * @package App\Controller\v1
 */
class CallPaypalController extends AbstractController{

    /**
     * @RequestMapping(path="notifation", methods="get")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function notifation(RequestInterface $request, ResponseInterface $response)
    {
        return $response->json(['code' => 200, 'msg' => 'ok']);
    }
}
