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

    public function sign(string $code, string $handle ,string $timestamp, string $method = 'get') :string
    {
        if ($method == 'get'){
            $query = ['appkey' => $this->appKey,'code' => $code, 'handle' => $handle, 'timestamp' => time()];
            ksort($query);
            $temp = [];
            foreach ($query as $qey)
            {
                array_push($temp, $qey);
            }
            $str = implode('&',$temp);
            $sign = hash_hmac('sha256', $str,$this->appSecret);
        }else{
            $newCode = json_encode(['code' => $code]);
            $sign = hash_hmac('sha256', "{$newCode}{$timestamp}",$this->appSecret);
        }
        return $sign;
    }

    /**
     * 拼接授权请求地址
     * $redirectUri = 'https://sh.tquuqu.com/v1/index/call'
     * @param string $handle
     * @param string $scope
     * @param string $redirectUri
     * @return string
     */
    public function oauth(string $handle, string $scope = 'read_orders,write_orders', string $redirectUri = 'https://v.tquuqu.com') :string
    {
        return 'https://'. $handle .'.myshopline.com/admin/oauth-web/#/oauth/authorize?appKey='.$this->appKey.'&responseType=code&scope='.$scope.'&redirectUri='.$redirectUri;
    }

    /**
     * 请求token
     * @param string $uri
     * @param string $url
     * @param array $array
     * @return string
     */
    public function authToken(string $uri,string $url, array $array) :string
    {
        try {
            $result = parallel([
                function () use($uri, $url, $array) {
                    $client = new Client([
                        'base_uri' => $uri,
                        'handler' => HandlerStack::create(new CoroutineHandler()),
                        'timeout' => 5,
                        'swoole' => [
                            'timeout' => 10,
                            'socket_buffer_size' => 1024 * 1024 * 2
                        ]
                    ]);
                    $sign = $this->sign($array['code'],$array['handle'],$array['timestamp'], 'post');
                    echo 'sign = '. $sign. "\r\n";
                    $respone = $client->post($url,[
                        'body' => json_encode(['code' => $array['code']]),
                        'headers' => [
                            'appkey' => $array['appkey'],
                            'sign' => $sign,
                            'timestamp' => $array['timestamp'],
                            'Content-Type' => 'application/json'
                            ],
                    ]);
                    return [
                        'coroutine_id' => Coroutine::id(),
                        'code' => $respone->getStatusCode(),
                        'body' => $respone->getBody()->getContents(),
                        'content' => $respone->getReasonPhrase()
                    ];
                }
            ]);
            print_r($result);
            return $result[0]['body'] ?? '';
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }catch (\Throwable $e){
            print_r($e->getMessage());
        }
    }

    /**
     * 将运单号推送到shopline
     * @param string $uri
     * @param string $path
     * @param array $array
     * @param string $token
     * @return bool
     * @throws \Exception
     */
    public function fulfillment(string $uri, string $path, array $array, string $token) :bool
    {
        try {
            $result = parallel([
                function () use($uri, $path,$array,$token){
                    $client = new Client([
                        'base_uri' => $uri,
                        'handler' => HandlerStack::create(new CoroutineHandler()),
                        'timeout' => 5,
                        'swoole' => [
                            'timeout' => 10,
                            'socket_buffer_size' => 1024 * 1024 * 2
                        ]
                    ]);
                    $respone = $client->post($path,[
                        'body' => json_encode([
                            'fulfillment' => [
                                'tracking_info' => [
                                    'company' => $array['company'],
                                    'number' => $array['number'],
                                    'url' => $array['url']
                                ]
                            ]
                        ]),
                        'headers' => [
                            'Authorization' => $token,
                            'Content-Type' => 'application/json'
                        ],
                    ]);
                    return [
                        'coroutine_id' => Coroutine::id(),
                        'code' => $respone->getStatusCode(),
                        'body' => $respone->getBody()->getContents(),
                        'content' => $respone->getReasonPhrase()
                    ];
                }
            ]);
            if (!isset($result[0]['body'])){
                throw new \Exception('请求shopline失败');
            }
            return true;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }catch (\Throwable $e){
            throw new \Exception($e->getMessage());
        }
    }
}
