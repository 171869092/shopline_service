<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Service;
use App\Service\EasyParcel\EasyParcelService;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Guzzle\CoroutineHandler;
use Psr\Container\ContainerInterface;
use Hyperf\Di\Annotation\Inject;
use Symfony\Component\Console\Input\InputOption;


/**
 * @Command
 */
#[Command]
class PullServiceCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var Service
     */
    protected $service;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('pull:service');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Hyperf Demo Command');
        $this->addOption('api', '', InputOption::VALUE_REQUIRED, 'apikey', 'Hyperf');
        $this->addOption('auth', '', InputOption::VALUE_REQUIRED, 'authkey', 'Hyperf');
        $this->addOption('country', '', InputOption::VALUE_REQUIRED, 'country', 'Hyperf');
    }

    public function handle()
    {
        $this->line("~~~ start ~~~", 'info');
        $api = $this->input->getOption('api');
        $auth = $this->input->getOption('auth');
        $country = $this->input->getOption('country');
//        $api = $this->input->getArgument('api');
//        $auth = $this->input->getArgument('auth');
        if (!$api || !$auth || !$country){
            return '未找到参数';
        }
        $params = [
            'authentication' => $auth,
            'api' => $api,
            'bulk' => [
                [
                    'pick_code' => '409015',
                    'pick_state' => 'sgr',
                    'pick_country' => 'SG',
                    'send_code' => '059897',
                    'send_state' => strtolower($country),      //. 代表发往哪个国家
                    'send_country' => $country, //. 代表发往哪个国家
                    'weight' => '30'
                ]
            ]
        ];
        $uri = 'http://connect.easyparcel.sg/';
        $path = '?ac=MPRateCheckingBulk';
        $client = new Client([
            'base_uri' => $uri,
            'handler' => HandlerStack::create(new CoroutineHandler()),
            'timeout' => 5,
            'swoole' => [
                'timeout' => 10,
                'socket_buffer_size' => 1024 * 1024 * 2
            ]
        ]);
        $respone = $client->post($uri.$path, ['body' => json_encode($params)]);
        $result = $respone->getBody()->getContents();
        $result = json_decode($result, true);
        print_r($result);
        foreach ($result['result'][0]['rates'] as $v){
            $v['dropoff_point'] = json_encode($v['dropoff_point']);
            $v['pickup_point'] = json_encode($v['pickup_point']);
            $v['country'] = $country;
            $this->service->create($v);
//            $this->service->insert($v);
        }
        $this->line("~~~ end ~~~", 'info');
    }
}
