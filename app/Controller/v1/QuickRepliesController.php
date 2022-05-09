<?php

declare(strict_types=1);

namespace App\Controller\v1;


use App\Constants\ErrorCode;
use App\Exception\UserErrorException;
use App\Model\CustomReact;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;

/**
 * Class QuickRepliesController
 * @package App\Controller\v1
 * @Controller()
 */
class QuickRepliesController extends AbstractController
{
    /**
     * @return mixed
     * @RequestMapping (path="list",methods="get,post")
     * 获取快捷回复列表
     *
     */
    public function list()
    {
        $user_id = $this->auth->guard('jwt')->user()->getId();
        $list = CustomReact::where(['user_id'=>$user_id])->orderBy('update_time','desc')->get(['id','content'])->toArray();
        return $this->response->json(['code'=>200,'msg'=>'Success','data'=>$list]);
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     * @PostMapping  (path="create-replies")
     * 创建快捷回复
     */
    public function createReplies(){
        $param = $this->request->all();
        $user_id = $this->auth->guard('jwt')->user()->getId();
        if(!isset($param['content']) || empty($param['content'])){
            throw new UserErrorException(ErrorCode::PARAM_ERROR);
        }
        //过滤重复提交
        $redis =  ApplicationContext::getContainer()->get(Redis::class);
        $key = md5($user_id.'=>'.$param['content']);
        $check = $redis->get($key);
        if($check){
            throw new UserErrorException(ErrorCode::REPEAT_SUBMISSION_ERROR);
        }else{
            $redis->setex($key,10,date('Y-m-d H:i:s'));
        }
        $field = [
          'user_id'=>$user_id,
            'content'=>$param['content']
        ];
        CustomReact::create($field);
        return $this->response->json(['code'=>200,'msg'=>'Success']);
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     * @PostMapping (path="update-replies")
     */
    public function updateReplies(){
        $param = $this->request->all();
        $user_id = $this->auth->guard('jwt')->user()->getId();
        (new CustomReact())->where(['user_id'=>$user_id,'id'=>$param['id']])->update(['content'=>$param['content']]);
        return $this->response->json(['code'=>200,'msg'=>'Success','data'=>$param]);
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     * @GetMapping (path="del-replies")
     */
    public function deleteReplies(){
        $user_id = $this->auth->guard('jwt')->user()->getId();
        (new CustomReact())->where(['user_id'=>$user_id,'id'=>$this->request->input('id')])->delete();
        return $this->response->json(['code'=>200,'msg'=>'Success']);
    }

}
