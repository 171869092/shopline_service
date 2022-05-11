<?php
declare(strict_types=1);
namespace App\Common;
use Hyperf\Config\Annotation\Value;
use Hyperf\Di\Annotation\Inject;

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
}
