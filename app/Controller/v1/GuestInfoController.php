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
use App\Annotation\NotAuth;
use App\Collector\SocketUserCollector;
use App\Constants\ErrorCode;
use App\Exception\UserErrorException;
use App\Model\Dialogue;
use App\Model\GuestUser;
use App\Model\User;
use App\Service\GuestManager\GuestInfoService;
use App\Service\GuestManager\GuestResolveService;
use Exception;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Qbhy\HyperfAuth\AuthManager;
use Qbhy\HyperfAuth\Guard\JwtGuard;

/**
 * Class GuestInfoController
 * @Controller()
 * @package App\Controller
 */
class GuestInfoController
{
    /**
     * GuestManager
     * @Inject()
     * @var AuthManager
     */
    protected $auth;

    /**
     * 搜索聊天记录
     * @GetMapping(path="search")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function search(RequestInterface $request, ResponseInterface $response)
    {
        $find = $request->input("find");
        $user = $this->auth->guard('jwt')->user();
        $data["visitors"] = Dialogue::where('user_id', $user->getId())
            ->where("guest_name", "like", "%{$find}%")
            ->orderBy("id", "desc")
            ->get()
            ->toArray();
        $data["message"] = Dialogue::where('user_id', $user->getId())
            ->where("content", "like", "%{$find}%")
            ->orderBy("id", "desc")
            ->get()
            ->toArray();
        return $response->json(['code' => 200, 'msg' => 'Success', 'data'=> $data]);
    }

    /**
     * 获取游客信息
     * @GetMapping(path="detail")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function detail(RequestInterface $request, ResponseInterface $response) {
        $guestId = $request->input("guest_id");
        $auth = $this->auth->guard('jwt');
        $guest = GuestUser::where(["id"=> $guestId, "user_id"=> $auth->user()->getId()])->first();
        if (!$guest) {
            throw new UserErrorException(ErrorCode::NOT_EXIST_ERROR, ["Customer"]);
        }
        return $response->json(['code' => 200, 'msg' => 'Success', 'data'=> $guest->toArray()]);
    }

    /**
     * 修改游客信息
     * @PostMapping(path="modify")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function modify(RequestInterface $request, ResponseInterface $response) {
        $guestId = $request->input("guest_id");
        $auth = $this->auth->guard('jwt');
        $guest = GuestUser::where(["id"=> $guestId, "user_id"=> $auth->user()->getId()])->first();
        if (!$guest) {
            throw new UserErrorException(ErrorCode::NOT_EXIST_ERROR, ["Customer"]);
        }
        [$found] = $request->hasInput(["guest_name", "email", "phone", "note"]);
        if (empty($found)) {
            throw new UserErrorException(ErrorCode::PARAM_ERROR);
        }
        foreach ($found as $key) {
            $guest->$key = $request->input($key);
        }
        $guest->save();
        $user = $guest->toArray();
        defer(function () use ($user) {
            $fds = SocketUserCollector::getUserFd(2, $user["id"]);
            foreach ($fds as $fd) {
                SocketUserCollector::modifyUser($fd, $user);
            }
        });

        return $response->json(['code' => 200, 'msg' => 'Success']);
    }

    /**
     * 删除游客信息
     * @PostMapping(path="delete")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     * @throws Exception
     */
    public function delete(RequestInterface $request, ResponseInterface $response) {
        $guestId = $request->input("guest_id");
        $auth = $this->auth->guard('jwt');
        $guest = GuestUser::where(["id"=> $guestId, "user_id"=> $auth->user()->getId()])->first();
        if (!$guest) {
            throw new UserErrorException(ErrorCode::NOT_EXIST_ERROR, ["Customer"]);
        }
        // 删除聊天记录
        Dialogue::where("guest_id", $guestId)->delete();
        $guest->delete();
        return $response->json(['code' => 200, 'msg' => 'Success']);
    }

    /**
     * 游客列表
     * @RequestMapping(path="guest-list")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function guestList(RequestInterface $request, ResponseInterface $response) {
        $user = $this->auth->guard('jwt')->user();
        // 联系状态 0没有联系 1沟通过 2结束
        $is_contact = $request->input("is_contact");
        // 搜索
        $find = (string)$request->input("find");
        $page = (int)$request->input("page", 1) ?: 1;
        $query = GuestUser::when($request->input("is_online"), function ($query) use ($user) {
                $guestIds = SocketUserCollector::getOnlineGuestIds($user["id"]);
                return $query->whereIn("id", $guestIds);
            })
            ->when($is_contact !== null, function ($query) use ($is_contact) {
                return $query->where("is_contact", $is_contact);
            })
            ->when($find !== "", function ($query) use ($find) {
                return $query->where(function ($query) use ($find) {
                    return $query->where("guest_name", "like", "%{$find}%")
                        ->orWhere("email", "like", "%{$find}%");
                });
            });
        $count = $query->count();
        $online = $query->orderBy("id", "desc")
            ->forPage($page, 10)
            ->get()
            ->toArray();
        foreach ($online as &$item) {
            $item["online_time"] = SocketUserCollector::getUserById(2, $item["id"])["online_time"] ?? "0";
        }
        return $response->json(['code' => 200, 'msg' => 'Success', "data"=> $online, "total"=> $count]);
    }

    /**
     * 游客获取token
     * @RequestMapping(path="token")
     * @NotAuth()
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param GuestResolveService $guestResolveService
     * @param GuestInfoService $guestInfoService
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Throwable
     */
    public function token(RequestInterface $request, ResponseInterface $response, GuestResolveService $guestResolveService, GuestInfoService $guestInfoService) {
        $chatra_id = $request->input("chatra_id");
        if (!$chatra_id) {
            throw new UserErrorException(ErrorCode::PARAM_ERROR);
        }
        $user = User::where("chatra_id", $chatra_id)->first();
        if (!$user) {
            throw new UserErrorException(ErrorCode::PARAM_ERROR);
        }
        $auth = $this->auth->guard('jwt-guest');
        if ($auth->check()) {
            if ($auth instanceof JwtGuard) {
                try {
                    $token = $auth->refresh();
                } catch (Exception $exception) {}
            }
        }
        if (empty($token)) {
            $refererInfo = $guestResolveService->parseRequest();
            $guestUser = GuestUser::create(array_merge([
                'user_id'=> $user->id,
                'guest_name'=> '',
                'email'=> '',
            ], $refererInfo));
            $token = $auth->login($guestUser);
        }
        $guestUser = $auth->user($token);
        $guestInfoService->trace($guestUser["id"], $request->input("referer", $request->header("referer")));
        return $response->json(['code'=> 200, 'msg'=> 'Success', 'data'=> ['token'=> $token]]);
    }

    /**
     * 收集客户端信息
     * @RequestMapping(path="adopt")
     * @NotAuth()
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param GuestResolveService $guestResolveService
     * @param GuestInfoService $guestInfoService
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function adopt(RequestInterface $request, ResponseInterface $response, GuestResolveService $guestResolveService, GuestInfoService $guestInfoService) {
        $auth = $this->auth->guard('jwt-guest');
        if ($auth->check()) {
            /** @var GuestUser $user */
            $user = $auth->user();
            $refererInfo = $guestResolveService->parseRequest();
            if (array_key_exists("user_id", $refererInfo)) {
                unset($refererInfo["user_id"]);
            }
            $user->update($refererInfo);
            $fds = SocketUserCollector::getUserFd(2, $user["id"]);
            foreach ($fds as $fd) {
                SocketUserCollector::modifyUser($fd, $user->toArray());
            }
            $guestInfoService->trace($user["id"], $request->input("referer", $request->header("referer")));
        }
        return $response->json(['code'=> 200, 'msg'=> 'Success']);
    }
}
