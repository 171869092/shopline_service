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
use App\Constants\ErrorCode;
use App\Kernel\Oauth\WeChatFactory;
use App\Model\Store;
use App\Model\User;
use App\Model\UserBelong;
use App\Model\WechatApplet;
use App\Service\User\UserService;
use Hyperf\Contract\ConfigInterface;
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
     * @Inject()
     * @var UserService
     */
    protected $userService;

    /**
     * @Inject()
     * @var WeChatFactory
     */
    protected $wechatService;

    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @RequestMapping(path="signin", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function signin(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $post = $request->post();
            $email = $post['phone'];
            $password = $post['password'];
            if (empty($email) || empty($password)){
                throw new \Exception('Account or passwor-d empty');
            }
            /** @var User $user */
            $user = User::query()->where(['phone' => $email, 'password' => $password])->first();
            if (empty($user)){
                throw new \Exception('Account or password error');
            }
            $data = $this->auth->login($user);
            return $response->json(['code' => 200, 'msg' => 'ok', 'data'=> $data]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * sign up
     * @RequestMapping(path="signup", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function signup(RequestInterface $request, ResponseInterface $response)
    {
        try {
            if (empty($request->post('phone')) || empty($request->post('password'))){
                throw new \Exception('Params error');
            }
            if ($request->post('password') !== $request->post('passwords')){
                throw new \Exception('Two inconsistent passwords');
            }
            $user = $this->userService->save($request->post());
            $data = $this->auth->login($user);
            return $response->json(['code' => 200, 'msg' => 'ok','data' => $data]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * install
     * @RequestMapping(path="install", methods="get")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function install(RequestInterface $request, ResponseInterface $response)
    {
        try {
            if (!$get = $request->all()){
                throw new \Exception('Params error');
            }
            $token = $this->userService->generateToken($get, $get['hamc']);
            return $response->json(['code' => 200, 'msg' => 'ok', 'data' => $token]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * @RequestMapping(path="live", methods="get")
     */
    public function live(RequestInterface $request, ResponseInterface $response)
    {
        echo 33333;
        echo "\r\n";
        print_r($this->wechatService->create()->access_token->getToken());
        return $response->json(['msg' => 'ok']);
    }

    /**
     * @RequestMapping(path="index", methods="get")
     */
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        echo 33333;
        return $response->json(['msg' => 'ok']);
    }

    /**
     * @RequestMapping(path="wxapp_login", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function wxapp_login(RequestInterface $request, ResponseInterface $response)
    {
        try {
            if (!$params = $request->post()){
                throw new \Exception('数据错误');
            }
            //参数兼容
            if(is_array($params["invitecode"])){
                $params["user_center"] = $params["invitecode"]['user_center'] ?? 0;
                $params["invitecode"] = $params["invitecode"]['invitationCode'];
            }
            if (empty($params['encrypt_id'])) {
                throw new \Exception('缺少encrypt_id参数！');
            }
            $wechat = $this->wechatService
                ->mini()
                ->auth
                ->session($params['code']);
            if (!$wechat){
                throw new \Exception('获取用户小程序失败');
            }
            if (!isset($wechat['openid']) && empty($wechat['openid'])){
                throw new \Exception($wechat['errcode'] . ' \ ' . $wechat['errmsg']);
            }
            echo "openid = {$wechat['openid']}\r\n";
            $user = $this->userService->findField('openid', $wechat['openid']);
            echo "\r\n user \r\n";
            $isUniond = false;
            if (!$user->id && isset($wechat['unionid'])){
                #. 兼容第三方登陆 unionid是唯一标示
                $user = $this->userService->findField('unionid', $wechat['unionid']);
                if (!$user){
                    $user->save(['openid' => $wechat['openid']]);
                    $isUniond = true;
                }
            }
            #. 邀请人
            $partner = !empty($params['invitecode']) ? $this->userService->findField('invitecode', $params['invitecode']) : [];
            if (!$user->id){
                echo "\r\n yes~ \r\n";
                #. 用户未注册
                if ($partner && strlen($partner['openid']) > 28) throw new \Exception('您的邀请人账号已销毁，请确认邀请人信息');
                $default_partner = 32;
                $partner_id = !empty($partner['id']) ? $partner['id'] : $default_partner;
                $belong = $partner ? $this->userService->findField('user_id', $partner_id, UserBelong::query()) : [];
                if (!$belong){
                    $belong = [];
                    $belong['user_id'] = $default_partner;
                    $belong['partner'] = $default_partner;
                    $belong['store'] = $default_partner;
                    $belong['gold_store'] = $default_partner;
                    $belong['gold_store_a'] = $default_partner;
                }
                // 获取真实IP
                $ip = $this->userService->getRealIp($request->getHeaders(),$request->server('remote_addr'));
                echo "ip = {$ip} \r\n";
                $time = time();
                $datetime = date('Y-m-d H:i:s');
                $applet_id = $this->userService->findField('encrypt_id', $params['encrypt_id'], WechatApplet::query());
                #. 生成邀请码
                $invitecode = $this->container->get(IdGeneratorInterface::class)->generate();
                echo "invitecode = {$invitecode}\r\n";
                $userid = $this->container->get(IdGeneratorInterface::class)->generate();
                echo "userid = {$userid} \r\n";
                $data = [
                    'applet_id' => $applet_id->id,
                    'openid' => $wechat['openid'],
                    'unionid' => isset($wechat['unionid']) ? $wechat['unionid'] : '',
                    'user_id' => $userid,
                    'username' => isset($params['nickName']) ? $this->userService->filterEmoji($params['nickName']) : '',
                    'nickname' => isset($params['nickName']) ? $this->userService->filterEmoji($params['nickName']) : '',
                    'avatar' => isset($params['avatarUrl']) ? $params['avatarUrl'] : '',
                    'city' => isset($params['city']) ? $params['city'] : '',
                    'province' => isset($params['province']) ? $params['province'] : '',
                    'country' => isset($params['country']) ? $params['country'] : '',
                    'gender' => isset($params['gender']) ? $params['gender'] : '',
                    'language' => isset($params['language']) ? $params['language'] : '',
                    'prevtime' => $time,
                    'logintime' => $time,
                    'jointime' => $time,
                    'loginip' => $ip,
                    'joinip' => $ip,
                    'status' => 'normal',
                    'invitecode' => $invitecode,
                    'partner_id' => $belong['partner'] ?? $default_partner,
                    'parent_id' => $belong['user_id'],
                    'createtime' => $datetime,
                    'source' => 'wechat',
                    'is_app' => 0,
                    'has_star' => intval($partner ? $partner['has_star'] : '0') // 如果邀请人用户、不是星标会员那就为0，新用户注册
                ];
                if (!$user = $this->userService->add($data)){
                    throw new \Exception('添加失败');
                }
                $belongArr['user_id'] = $user->id;
                $belongArr['parent_id'] = $belong['user_id'];
                $belongArr['partner'] = $belong['partner'];
                $belongArr['store'] = $belong['store'];
                $belongArr['gold_store'] = $belong['gold_store'];
                $belongArr['gold_store_a'] = $belong['gold_store_a'];
                $belongArr['createtime'] = $datetime;
                $this->userService->insertRegular($belongArr);

                #. 邀请记录
                $iarr['user_id'] = $belong['user_id'];
                $iarr['invite_id'] = $user->id;
                $iarr['createtime'] = $datetime;
                $this->userService->addInvite($iarr);
            } else if (!$user['unionid'] && isset($wechat['unionid'])){
                if ($isUniond === true){
                    $this->userService->updates(['unionid' => $wechat['unionid'], 'openid' => $wechat['openid']]);
                }else{
                    $this->userService->updates(['unionid' => $wechat['unionid'], 'openid' => $wechat['openid']], true);
                }
            }
            $result = $this->auth->login($user);
            return $response->json(['code' => 200, 'msg' => 'ok', 'data'=> $user->toArray(), 't' => $result]);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }
}
