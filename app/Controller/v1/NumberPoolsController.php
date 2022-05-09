<?php
declare(strict_types=1);

namespace App\Controller\v1;


use App\Constants\ErrorCode;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\Di\Annotation\Inject;


class NumberPoolsController extends AbstractController
{
    /**
     * @RequestMapping(path="list", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function list(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $params = $request->all();

        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }
}
