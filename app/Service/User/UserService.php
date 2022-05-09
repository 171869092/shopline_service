<?php
declare(strict_types=1);
namespace App\Service\User;

use App\Collector\SocketUserCollector;
use App\Event\RegisterScriptEvent;
use App\Model\Store;
use App\Model\Timezone;
use App\Model\User;
use App\Model\UserBelong;
use App\Model\UserInvite;
use App\Service\GuestManager\GuestInfoService;
use Hyperf\Redis\Redis;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Snowflake\IdGeneratorInterface;
use Hyperf\Utils\ApplicationContext;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\EventDispatcher\EventDispatcherInterface;
use App\Event\EmailEvent;
use Hyperf\Config\Annotation\Value;
use function Swoole\Coroutine\Http\get;
use function Swoole\Coroutine\Http\post;

class UserService{

    /**
     * @Inject
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @Inject
     * @var Redis
     */
    protected $redis;

    /**
     * @Inject
     * @var IdGeneratorInterface
     */
    protected $snowflake;

    /**
     * @Value("aws.apiKey")
     */
    private $apiKey;

    /**
     * @Value("aws.apiSecret")
     */
    private $apiSercet;

    /**
     * @Inject
     * @var User
     */
    private $user;

    const DEFAULT_PARTNER = 32;

    /**
     * create user
     * @param array $data
     * @return int
     */
    public function save(array $data) :User
    {
        $data['chatra_id'] = $this->snowflake->generate();
        $user = User::create($data);
        if (!$user->getId()){
            throw new \Exception('Create fail');
        }
        return $user;
    }

    /**
     * save store
     * @param array $params
     * @param int $userId
     * @return bool
     * @throws \Exception
     */
    public function saveStore(array $params, int $userId) :bool
    {
        $token = $this->generateToken($params['shop'], $params['hmac']);
        Store::create([
            'store_url' => $params['shop'],
            'user_id' => $userId,
            'platform' => 1,
            'create_time' => date('Y-m-d H:i:s'),
            'api_token' => $token ?? '',
        ]);;
        $this->eventDispatcher->dispatch(new RegisterScriptEvent((string)$params['shop'], (string)$token));
        return true;
    }

    /**
     * generate auth url
     * @param string $shop
     * @param string $apiKey
     * @param string $redirectUri
     * @param string $scope
     * @return string
     */
    public function generateUrl(string $shop, string $apiKey, string $redirectUri = '',string $scope = 'read_orders,write_orders,write_products,read_products,read_fulfillments,write_fulfillments,read_inventory,write_inventory,read_customers,read_content,read_themes,write_themes,read_script_tags,write_script_tags') :string
    {
        return 'https://' . $shop . '/admin/oauth/authorize?client_id=' . $apiKey . '&scope=' . $scope . '&redirect_uri=' . urlencode($redirectUri);
    }

    /**
     * generate token
     * @param array $params
     * @param string $hmac
     * @return string
     * @throws \Exception
     */
    public function generateToken(array $params, string $hmac) :string
    {
        try {
            $check = $this->checkToken($params['shop']);
            if ($check){
                return '';
            }
            $params = array_diff_key($params, ['hmac' => '']);
            ksort($params);
            $computed_hmac = hash_hmac('sha256', http_build_query($params), $this->apiSercet);
            if (hash_equals($hmac, $computed_hmac)){
                $query = [
                    'client_id' => $this->apiKey,
                    'client_secret' => $this->apiSercet,
                    'code' => $params['code']
                ];
                $access_token_url = "https://" . $params['shop'] . "/admin/oauth/access_token";
                $res = post($access_token_url,http_build_query($query));
                if (!$res->getBody()){
                    throw new \Exception('Get token error');
                }
                $result = @json_decode($res->getBody(), true);
                echo "result: \r\n";
                print_r($result);
                $access_token = $result['access_token'];
            }else{
                throw new \Exception('This request is NOT from Shopify!');
            }
            return $access_token;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * check store exists
     * @param string $storeUrl
     * @return bool
     */
    public function checkToken(string $storeUrl) :bool
    {
        $store = Store::where(['store_url' => $storeUrl,'store_status' => 1])->first(['api_token']);
        if ($store && !empty($store->api_token)){
            return true;
        }
        return false;
    }

    /**
     * channged password
     * @param array $post
     * @return bool
     * @throws \Exception
     */
    public function channgedPwd(array $post) :bool
    {
        try {
            $user = User::where(['email' => $post['email']]);
            if (!$user->first()){
                throw new \Exception('Not found user');
            }
            $update = $user->update(['password' => $post['password'], 'update' => date('Y-m-d H:i:s')]);
            if (!$update){
                throw new \Exception('Update fail');
            }
            return true;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * retrieve password
     * @param array $post
     * @return bool
     * @throws \Exception
     */
    public function retrievePwd(array $post) :bool
    {
        try {
            $code = $this->redis->get('retrieve_'. $post['email']);
            if (!$code){
                throw new \Exception('Retrieve key not found');
            }
            #. check pwd
            if ($post['password'] !== $post['passwords']){
                throw new \Exception('Two inconsistent password entries');
            }
            if ($post['code'] !== $code){
                throw new \Exception('Code check error');
            }
            User::where(['email' => trim($post['email'])])->update(['password' => trim($post['password'])]);
            #. 删除redis code
            $this->redis->del('retrieve_'. $post['email']);
            return true;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * send code
     * @param array $post
     * @return bool
     */
    public function send(array $post) :bool
    {
        $code = $this->genCode();
        $data = [
            'email' => $post['email'],
            'verfCode' => <<<EOF
<p>Hello! <br/>
Someone (most likely you) requested to reset your Chat cat password. <br/>
Below is the verification code for resetting the password:<br/>
Reset password verification code effective time: 5 minutes.<br/>
<a href="#">{$code}</a><br/>
Ignore this email if you’ve gotten it by mistake. Your current password is safe and sound.<br/>
If you have any other questions, please click reply in this email to describe the questions, and we will contact you at the first time<br/>
Thank you!<br/>
Chat cat</p>
EOF,
            'title' => 'Chatcat Verification code'
        ];
        $this->eventDispatcher->dispatch(new EmailEvent($data['email'],$data['title'],$data['verfCode']));
        echo "\r\n send-ok \r\n";
        #. 将验证码存入redis
        $this->redis->set('retrieve_'. $post['email'], $code);
        return true;
    }

    /**
     * generate code
     * @param int $num
     * @return string
     */
    public function genCode(int $num = 6) :string
    {
        $str = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        $result = '';
        for ($i = 0; $i < $num; $i++) {
            $result .= $str[array_rand($str, 1)];
        }
        return $result;
    }

    /**
     * 获取用户头像
     * @param $userId
     * @return string
     */
    public function getUserAvatar($userId) {
        $avatar = $this->redis->get("snap_user_info:avatar_" . $userId);
        if ($avatar !== false) {
            return $avatar;
        }
        $avatar = User::where("id", $userId)->value("avatar") ?: "";
        $this->redis->setex("snap_user_info:avatar_" . $userId, 86400, $avatar);
        return $avatar;
    }

    /**
     * 获取客服在线状态
     * @param $userId
     * @return bool
     */
    public function getOnlineStatus($userId) {
        $user = User::find($userId);
        if (!$user || $user->is_online != "1") {
            return false;
        }
        return SocketUserCollector::hasUser(1, $userId);
    }

    /**
     * channged status
     * @param int $id
     * @param int $is_online
     * @return bool
     */
    public function channgedStatus(int $id, int $is_online) :bool {
        User::where(['id' => $id])->update(['is_online' => $is_online]);
        #. send notifation
        ApplicationContext::getContainer()->get(GuestInfoService::class)->customerNotify($id);
        return true;
    }

    /**
     * get userinfo
     * @param int $id
     * @return array
     */
    public function getUserInfo(int $id) :array
    {
        return User::where(['id' => $id])->first()->toArray();
    }

    /**
     * channged notifation
     * @param int $id
     * @param int $enbale
     * @return int
     */
    public function channgedNoti(int $id, int $notifation) :int
    {
        return User::where(['id' => $id])->update(['is_notifation' => $notifation, 'update_time' => date('Y-m-d H:i:s')]);
    }

    /**
     * get time zone
     * @return array
     */
    public function timeZone() :array
    {
        $zone = $this->redis->get('time_zone');
        if (!$zone){
            $data = Timezone::get()->toArray();
            foreach ($data as &$v){
                if (true !== strpos($v['area'],'/')){
                    unset($v);
                }
            }
            $this->redis->set('time_zone', json_encode($data));
        }else{
            $data = @json_decode($this->redis->get('time_zone'), true);
        }
        return $data;
    }

    /**
     * channged userinfo
     * @param array $params
     * @return int
     * @throws \Exception
     */
    public function channgedInfo(array $params) :int
    {
        if (!isset($params['id']) || empty($params['id'])){
            throw new \Exception('Params error');
        }
        $obj = User::where(['id' => $params['id']]);
        if (!$obj->exists()){
            throw new \Exception('Not found user');
        }
        return User::where(['id' => $params['id']])->update([
            'user_name' => $params['user_name'],
            'email' => $params['email'],
            'title' => $params['title'],
            'password' => $params['password'],
            'avatar' => $params['avatar'],
            'time_zone' => $params['time_zone'],
            'is_show' => $params['is_show'],
            'update_time' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * @param array $params
     * @return int
     * @throws \Exception
     */
    public function updatePwd(array $params) :int
    {
        if ($params['password'] !== $params['passwords']){
            throw new \Exception('Two inconsistent password entries');
        }
        if (!isset($params['id']) || empty($params['id'])){
            throw new \Exception('Params error');
        }
        $user = User::where(['id' => $params['id']])->first();
        if ($user->password !== $params['old_password']){
            throw new \Exception('Old password error');
        }
        return User::where(['id' => $params['id']])->update(['password' => $params['passwords'], 'update_time' => date('Y-m-d H:i:s')]);
    }

    public function findField(string $field, $value, object $obj = null) :object
    {
        if (is_null($obj)){
            $obj = $this->user;
        }
        return $obj->where($field,$value)->first() ?? $obj;
    }

    /**
     * 获取客户真实IP
     * @param $server
     * @param null $remote_addr
     * @return bool|mixed|string|null
     */
    public function getRealIp($server, $remote_addr = null) {
        $ip=FALSE;
        // 客户端IP 或 NONE
        if(!empty($server["x-real-ip"])){
            $ip = $server["x-real-ip"][0] ?? false;
        }
        // 多重代理服务器下的客户端真实IP地址（可能伪造）,如果没有使用代理，此字段为空
        if (!empty($server['x-forwarded-for'])) {
            $ips = explode (", ", implode(', ', $server['x-forwarded-for']));
            for ($i = 0; $i < count($ips); $i++) {
                if (!preg_match("/^(10|172\\.(1[6-9]|2[0-9]|3[01])|192\\.168)\\./", $ips[$i])) {
                    $ip = $ips[$i];
                    break;
                }
            }
        }
        // 客户端IP 或 (最后一个)代理服务器 IP
        return ($ip ?: $remote_addr);
    }


    // 过滤掉emoji表情
    public function filterEmoji(string $str) :string
    {
        $str = preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);

        return $str;
    }

    /**
     * add
     * @param array $params
     * @return User
     */
    public function add(array $params) :User
    {
        return User::create($params);
    }

    public function insertRegular(array $params) :bool
    {
        if ($params['user_id']){
            return false;
        }
        $this->getParentStr($params['parent_id'], '|');
        $belong = UserBelong::where(['user_id' => $params['user_id']])->first();
        $result = $belong ? UserBelong::where(['user_id' => $params['user_id']])->update($params) : UserBelong::create($params);
        if (!$result){
            return false;
        }
        return true;
    }

    /**
     * @param null $parentId
     * @param string $str
     * @return mixed|string
     */
    public function getParentStr($parentId = null, $str = '|')
    {
        $str = $str . $parentId . '|';
        if ($parentId) {
            $belongInfo = UserBelong::where('user_id', $parentId)->first();
            if ($parentId != self::DEFAULT_PARTNER || !$belongInfo || $parentId != $belongInfo->user_id) {
                $str = $this->getParentStr($belongInfo->parent_id, $str);
            }
        }
        return $str;
    }

    /**
     * @param array $params
     * @return bool
     */
    public function addInvite(array $params) :bool
    {
        return UserInvite::insert($params);
    }

    /**
     * @param array $params
     * @return bool
     */
    public function updates(array $params, bool $type = false) :int
    {
        if (!$type){
            return User::where(['unionid' => $params['unionid']])->update(['openid' => $params['openid']]);
        }else{
            return User::where(['openid' => $params['openid']])->update(['unionid' => $params['unionid']]);
        }
    }
}

