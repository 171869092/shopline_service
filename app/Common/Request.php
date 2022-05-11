<?php
declare(strict_types=1);
namespace App\Common;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Hyperf\Config\Annotation\Value;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\Utils\Coroutine;

class Request
{
    /**
     * @Value("shopline.dev.app_key")
     */
    protected $appKey;

    /**
     * @Value("shopline.dev.app_secret")
     */
    protected $appSecret;

    /**
     * @Value("shopline.version")
     */
    protected $version;

    /**
     * @Value("shopline.url")
     */
    protected $url;

    public function sign(string $code, string $handle) :string
    {
        $query = ['appkey' => $this->appKey,'code' => $code, 'handle' => $handle, 'timestamp' => time()];
        ksort($query);
        $temp = [];
        foreach ($query as $qey)
        {
            array_push($temp, $qey);
        }
        $str = implode('&',$temp);
        return hash_hmac('sha256', $str,$this->appSecret);
    }

    /**
     * 拼接授权请求地址
     * @param string $handle
     * @param string $scope
     * @param string $redirectUri
     * @return string
     */
    public function oauth(string $handle, string $scope = 'read_orders,write_orders', string $redirectUri = 'https://sh.tquuqu.com/v1/index/call') :string
    {
        return 'https://'. $handle .'.myshopline.com/admin/oauth-web/#/oauth/authorize?appKey='.$this->appKey.'&responseType=code&scope='.$scope.'&redirectUri='.$redirectUri;
    }

    public function requestAuth(string $url, string $method = 'get')
    {
        try {
            $result = parallel([
                function () use($url) {
                    $client = new Client([
                        'base_uri' => $url,
                        'handler' => HandlerStack::create(new CoroutineHandler()),
                        'timeout' => 5,
                        'swoole' => [
                            'timeout' => 10,
                            'socket_buffer_size' => 1024 * 1024 * 2
                        ]
                    ]);
                    $respone = $client->get($url);
                    return [
                        'coroutine_id' => Coroutine::id(),
                        'code' => $respone->getStatusCode(),
                        'body' => $respone->getBody()->getContents(),
                        'content' => $respone->getReasonPhrase()
                    ];
                }
            ]);
            print_r($result);
            return true;
        }catch (\Exception $e){

        }catch (\Throwable $e){

        }
    }
}