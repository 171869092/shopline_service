<?php
declare(strict_types=1);

namespace App\Controller\admin;
use App\Constants\ErrorCode;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Context;
use Qbhy\HyperfAuth\Annotation\Auth;
use Qbhy\HyperfAuth\AuthManager;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\JwtAuthMiddleware;
use App\Annotation\NotAuth;

class IndexController extends AbstractController
{
    /**
     * @RequestMapping(path="index", methods="get,post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function index()
    {

    }
}
