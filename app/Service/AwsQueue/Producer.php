<?php

declare(strict_types=1);

namespace App\Service\AwsQueue;

use Aws\Credentials\Credentials;
use Aws\Sqs\SqsClient;
use Hyperf\Config\Annotation\Value;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;

class Producer implements ProducerInterface
{
    /**
     * @Value("aws.id")
     */
    private $awsId;
    /**
     * @Value("aws.secret")
     */
    private $awsSecret;
    /**
     * @var string queue
     */
    private $queueUrl;
    /**
     * @var SqsClient
     */
    private $SqsClient;
    /**
     * @Inject
     * @var ContainerInterface
     */
    private $container;

    public function __construct(string $queueUrl = null)
    {
        if (empty($queueUrl)){
            $this->queueUrl = 'https://sqs.us-east-1.amazonaws.com/606841860032/dx_queue.fifo';
        }else{
            $this->queueUrl = $queueUrl;
        }
        $credentialsClient = new Credentials($this->awsId,$this->awsSecret);
        $this->SqsClient = new SqsClient([
            'region' => 'us-east-1',
            'version' => '2012-11-05',
            'credentials' => $credentialsClient
        ]);
    }


    /**
     * 发送aws队列,
     * @param array $data
     * @param int $delay
     * @return mixed|null
     */
    public function run(array $data , int $delay = 0):array {
        try {
            $rand = $this->GenerateSku(true, true, false, '', 6);;
            $sqsClient = $this->SqsClient;
            $result = $sqsClient->sendMessage([
                'DelaySeconds' => $delay,
                'MessageBody' => json_encode($data),
                'QueueUrl' => $this->queueUrl,
                'MessageGroupId' => 'live-'.$rand,
                'MessageDeduplicationId' => 'live-'.$rand
            ]);
            return ['code' => -1, 'id' => $result->get('MessageId')];
        }catch (\Exception $e){
            print_r($e->getMessage() . "\n");
            return ['code'=> 403, 'id' => null];
        }
    }

    /**
     * 生成sku--此处用来为队列Tag生成随机字符串
     * @return string
     */
    public function GenerateSku(bool $alpha = true,bool $nums = true,bool $usetime = false,string $string = '',int $length = 120) :string {
        $alpha = ($alpha == true) ? 'abcdefghijklmnopqrstuvwxyz' : '';
        $nums = ($nums == true) ? '1234567890' : '';

        if ($alpha == true || $nums == true || !empty($string)) {
            if ($alpha == true) {
                $alpha = $alpha;
                $alpha .= strtoupper($alpha);
            }
        }
        $randomstring = '';
        $totallength = $length;
        for ($na = 0; $na < $totallength; $na++) {
            $var = (bool)rand(0,1);
            if ($var == 1 && $alpha == true) {
                $randomstring .= $alpha[(rand() % mb_strlen($alpha))];
            } else {
                $randomstring .= $nums[(rand() % mb_strlen($nums))];
            }
        }
        if ($usetime == true) {
            $randomstring = $randomstring.time();
        }
        return($randomstring);
    }

    /**
     * product
     * @param array $data
     * @param int $delay
     * @return array
     */
    public function commonrun(array $data , int $delay = 0):array {
        try {
            $rand = $this->GenerateSku(true, true, false, '', 6);;
            $sqsClient = $this->SqsClient;
            $result = $sqsClient->sendMessage([
                'DelaySeconds' => $delay,
                'MessageBody' => json_encode($data),
                'QueueUrl' => "https://sqs.us-east-1.amazonaws.com/606841860032/dx_queue_common_product.fifo",  //拉取公共产品队列
                'MessageGroupId' => 'live-'.$rand,
                'MessageDeduplicationId' => 'live-'.$rand
            ]);
            return ['code' => -1, 'id' => $result->get('MessageId')];
        }catch (\Exception $e){
            print_r($e->getMessage() . "\n");
            return ['code'=> 403, 'id' => null];
        }
    }

    /**
     * @param int $num
     * @param bool $type
     * @return array|bool
     */
    public function receive(int $num = 10, $type = false) {
        try {
            $sqsClient = $this->SqsClient;
            $result = $sqsClient->receiveMessage([
                'AttributeNames' => ['SentTimestamp'],
                'MaxNumberOfMessages' => $num,
                'MessageAttributeNames' => ['All'],
                'QueueUrl' => $this->queueUrl, // REQUIRED
                'WaitTimeSeconds' => 1,
            ]);
            if ($type === false){
                #. 先标记消费掉
                if ($mqMessages = $result->get('Messages')) {
                    foreach ($mqMessages as $message) {
                        $sqsClient->deleteMessage([
                            'QueueUrl' => $this->queueUrl, // REQUIRED
                            'ReceiptHandle' => $message['ReceiptHandle'] // REQUIRED
                        ]);
                    }
                }
            }
            return $result->toArray();
        }catch (\Exception $e){
            print_r($e->getMessage() . "\n");
            return false;
        }
    }
}
