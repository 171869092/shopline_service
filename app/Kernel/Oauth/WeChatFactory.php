<?php

declare(strict_types=1);

namespace App\Kernel\Oauth;


use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;
use EasyWeChat\Factory;

class WeChatFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    private $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class)->get('wechat.mini_program.default');
    }

    /**
     * @return \EasyWeChat\OfficialAccount\Application
     */
    public function create()
    {
        $app = Factory::officialAccount($this->config);
        $handler = new CoroutineHandler();

        // 设置 HttpClient，部分接口直接使用了 http_client。
        $config = $app['config']->get('http', []);
        $config['handler'] = $stack = HandlerStack::create($handler);
        $app->rebind('http_client', new Client($config));

        // 部分接口在请求数据时，会根据 guzzle_handler 重置 Handler
        $app['guzzle_handler'] = $handler;

        // 如果使用的是 OfficialAccount，则还需要设置以下参数
        $app->oauth->setGuzzleOptions([
            'http_errors' => false,
            'handler' => $stack,
        ]);
        return $app;
    }

    /**
     * @return \EasyWeChat\MiniProgram\Application
     */
    public function mini()
    {
        $app = Factory::miniProgram($this->config);
        $handler = new CoroutineHandler();

        // 设置 HttpClient，部分接口直接使用了 http_client。
        $config = $app['config']->get('http', []);
        $config['handler'] = $stack = HandlerStack::create($handler);
        $app->rebind('http_client', new Client($config));

        // 部分接口在请求数据时，会根据 guzzle_handler 重置 Handler
        $app['guzzle_handler'] = $handler;

        // 如果使用的是 OfficialAccount，则还需要设置以下参数
//        $app->auth->setGuzzleOptions([
//            'http_errors' => false,
//            'handler' => $stack,
//        ]);
        return $app;
    }
}
