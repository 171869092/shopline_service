<?php
declare(strict_types=1);
namespace App\Service\Notifation;

use App\Event\NotifationEvent;
use App\Service\AwsQueue\Producer;
use Hyperf\SocketIOServer\SocketIO;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Hyperf\Config\Annotation\Value;
use Hyperf\Di\Annotation\Inject;

class NotifationService {

    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @Value("aws.queue.send_notifation_c")
     */
    private $queueUrl;

    #. 推送信息
    public function SendMsg(array $params) :array {
        try {
            foreach ($this->field() as $vv){
                if (!isset($params[$vv]) || empty($params[$vv])){
                    throw new \Exception($vv . ',Not found');
                }
            }
            switch ($params['root_type']){
                case 1:
                    $roomName = 'notifation_server_'.$params['value'];;
                    break;
                case 2:
                    $roomName = 'notifation_client_'.$params['value'];;
                    break;
                default:
                    throw new \Exception('error');
            }
            #. 推送事件
            $this->eventDispatcher->dispatch(new NotifationEvent((int)$params['value'], (int)$params['root_type'], (array)$params));
            #. 向房间推送
            $socket = $this->container->get(SocketIO::class);
            #. 判断房间是否存在, 如果不存在则加入queue
            if (!$socket->adapter->clients((string) $roomName)){
                #. 加入queue
                $producer = new Producer($this->queueUrl);
                $producer->run([
                    'room' => $roomName,
                    'params' => $params
                ]);
            }else{
                $socket->of('/notifation')->to($roomName)->emit('send-msg', $params);
            }
            return ['code' => 200, 'msg' => 'ok'];
        }catch (\Exception $e){
            return ['code' => 400, 'msg' => $e->getMessage()];
        }
    }

    public function field() :array
    {
        return [
            'root_type','msg_json','value'
        ];
    }
}
