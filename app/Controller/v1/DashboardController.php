<?php

declare(strict_types=1);

namespace App\Controller\v1;

use App\Service\Dashboard\DashboardService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * Class DashboardController
 * @package App\Controller\v1
 * @Controller()
 */
class DashboardController extends AbstractController
{
    /**
     * @return \Psr\Http\Message\ResponseInterface
     * @RequestMapping (path="index",methods="get,post")
     * 获取仪表盘显示数据
     */
    public function index()
    {
        $result = (new DashboardService())->getDashboardData($this->auth->guard('jwt')->user()->getId());
        return $this->response->json(['code'=>200, 'msg' => 'Success', 'data'=>$result]);
    }
}
