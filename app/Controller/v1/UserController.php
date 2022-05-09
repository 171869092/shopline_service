<?php

declare(strict_types=1);

namespace App\Controller\v1;

use App\Constants\ErrorCode;
use App\Service\User\UserService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Annotation\NotAuth;
use Hyperf\Di\Annotation\Inject;

/**
 * Class UserController
 * @Controller()
 * @package App\Controller\v1
 */
class UserController
{
    /**
     * @Inject
     * @var UserService
     */
    protected $userService;

    /**
     * channged password
     * @RequestMapping(path="update", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function update(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $post = $request->post();
            if (!$post || !$post['password'] || !$post['email']){
                throw new \Exception('Params error');
            }
            $this->userService->channgedPwd($post);
            return $response->json(['code'=>200, 'msg' => 'ok']);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * retrieve password
     * @NotAuth()
     * @RequestMapping(path="retrieve", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function retrieve(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $post = $request->post();
            /**
             * type = code 发送验证码信息
             * type = reset 重制密码
             */
            if (!isset($post) || empty($post)){
                throw new \Exception('Params not found');
            }
            if ($post['type'] == 'code'){
                if (!isset($post['email']) || empty($post['email'])){
                    throw new \Exception('Please fill in your email address!');
                }
                #. gen code
                $this->userService->send($post);
                return $response->json(['code'=>200, 'msg' => 'ok']);
            }else{
                $result = $this->userService->retrievePwd($post);
                return $response->json(['code'=>200, 'msg' => 'ok', 'data' => $result ?? -1]);
            }
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * @RequestMapping(path="channged", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function channged(RequestInterface $request, ResponseInterface $response){
        try {
            if (!$post = $request->post()){
                throw new \Exception('Params error');
            }
            $this->userService->channgedStatus((int)$post['id'], (int)$post['is_online']);
            return $response->json(['code'=>200, 'msg' => 'ok']);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * @RequestMapping(path="info", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function userinfo(RequestInterface $request, ResponseInterface $response)
    {
        try {
            if (!$post = $request->post()){
                throw new \Exception('Params error');
            }
            $result = $this->userService->getUserInfo($post['id']);
            return $response->json(['code'=>200, 'msg' => 'ok', 'data' => $result]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * channged notifation
     * @RequestMapping(path="notifation", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function notifation(RequestInterface $request, ResponseInterface $response)
    {
        try {
            if (!$post = $request->post()){
                throw new \Exception('Params error');
            }
            $this->userService->channgedNoti((int)$post['id'], (int)$post['notifation']);
            return $response->json(['code'=>200, 'msg' => 'ok']);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * get timezone
     * @RequestMapping(path="timeZone", methods="post")
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function timeZone(ResponseInterface $response)
    {
        try {
            $result = $this->userService->timeZone();
            return $response->json(['code'=>200, 'msg' => 'ok', 'data' => $result]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * channged userinfo
     * @RequestMapping(path="channged-info", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function channgedInfo(RequestInterface $request, ResponseInterface $response){
        try {
            if (!$post = $request->post()){
                throw new \Exception('Params error');
            }
            $this->userService->channgedInfo($post);
            return $response->json(['code'=>200, 'msg' => 'ok']);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * @RequestMapping(path="update-pwd", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function updatePwd(RequestInterface $request, ResponseInterface $response)
    {
        try {
            if (!$post = $request->post()){
                throw new \Exception('Params error');
            }
            $this->userService->updatePwd($post);
            return $response->json(['code'=>200, 'msg' => 'ok']);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }
}
