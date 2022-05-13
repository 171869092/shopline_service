<?php
namespace App\Service\EasyParcel;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Hyperf\Config\Annotation\Value;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\Utils\Coroutine;

class EasyParcelService
{
    /**
     * @Value("easyparcel.dev.api_key")
     */
    protected $appKey;

    /**
     * @Value("easyparcel.dev.auth_key")
     */
    protected $authKey;

    /**
     * @Value("easyparcel.dev.uri")
     */
    protected $uri;

    public function request() :array
    {
        $uri = $this->uri;
        $params = [];
        $path = '';
        $result = parallel([
            function () use($uri,$path, $params)
            {
                $client = new Client([
                    'base_uri' => $uri,
                    'handler' => HandlerStack::create(new CoroutineHandler()),
                    'timeout' => 5,
                    'swoole' => [
                        'timeout' => 10,
                        'socket_buffer_size' => 1024 * 1024 * 2
                    ]
                ]);
                $respone = $client->post($path, $params);
                return [
                    'coroutine_id' => Coroutine::id(),
                    'code' => $respone->getStatusCode(),
                    'body' => $respone->getBody()->getContents(),
                    'content' => $respone->getReasonPhrase()
                ];
            }
        ]);
        return $result;
    }
}
