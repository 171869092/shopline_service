<?php
declare(strict_types=1);
namespace App\Controller\v1;
use App\Constants\ErrorCode;
use App\Model\ScriptTag;
use App\Model\Store;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Controller;
use App\Annotation\NotAuth;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
/**
 * @NotAuth
 * Class WebhookController
 * @package App\Controller\v1
 */
class WebhookController extends AbstractController
{

    /**
     * @RequestMapping(path="uninstall", methods="post")
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function uninstall(RequestInterface $request, ResponseInterface $response)
    {
        try {
            #. del store,webhook,script tag
            $domain = $request->post('domain');
            if (!$domain){
                throw new \Exception('Domain not found');
            }
            Store::where(['store_url' => $domain])->update([
                'store_status' => 3,
                'update_time' => date('Y-m-d H:i:s')
            ]);
            ScriptTag::where(['store_url' => $domain])->delete();
            return $response->json(['code' => 200, 'msg' => 'ok']);
        }catch (\Exception $e){
            return $response->json(['code' => ErrorCode::NORMAL_ERROR, 'msg' => $e->getMessage()]);
        }
    }
}
