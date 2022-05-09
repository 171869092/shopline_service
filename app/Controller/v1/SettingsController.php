<?php

declare(strict_types=1);

namespace App\Controller\v1;

use App\Annotation\NotAuth;
use App\Constants\ErrorCode;
use App\Exception\UserErrorException;
use App\Model\User;
use App\Service\Settings\SettingsService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;

/**
 * Class SettingsController
 * @package App\Controller\v1
 * @Controller
 */
class SettingsController extends AbstractController
{
    /**
     * 获取配置信息
     * @GetMapping (path="index")
     */
    public function index()
    {
        $user_id = $this->auth->guard('jwt')->user()->getId();
        $result = (new SettingsService())->getSettings($user_id,$this->request->input('type'));
        return $this->response->json(['code'=>200, 'msg' => 'Success', 'data'=>$result]);
    }

    /**
     * 通过游客ID 获取配置信息
     * @NotAuth
     * @GetMapping (path="guest-setting")
     */
    public function guestSetting()
    {
        $user = User::where(['chatra_id'=>$this->request->input('chatra_id')])->first(['id','avatar']);
        if(!$user){
            throw new UserErrorException(ErrorCode::USER_NOT_EXIST_ERROR);
        }
        $result = (new SettingsService())->getSettings($user->id,$this->request->input('type'));
        $result['avatar'] = $user->avatar;
        return $this->response->json(['code'=>200, 'msg' => 'Success', 'data'=>$result]);
    }

    /**
     * 修改配置
     * @PostMapping (path="modify-settings")
     */
    public function modifySettings(){
        $request = $this->request->all();
        $user_id = $this->auth->guard('jwt')->user()->getId();
        $result = (new SettingsService())->modifySettings($user_id,$request);
        return $this->response->json(['code'=>200, 'msg' => 'Success', 'data'=>$result]);
    }
}
