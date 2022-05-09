<?php


namespace App\Service\GuestManager;

use App\Model\TimezoneMap;
use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\ApplicationContext;
use Swoole\Coroutine\Http\Client\Exception;
use function Swoole\Coroutine\Http\get;

/**
 * 游客来源解析
 * Class GuestResolveService
 * @package App\Service\GuestManager
 */
class GuestResolveService
{
    /**
     * 解析请求信息
     * @return array
     */
    public function parseRequest():array {
        $request = ApplicationContext::getContainer()->get(RequestInterface::class);
        // 获取真实IP
        $ip = $this->getRealIp($request->getHeaders(), $request->server('remote_addr'));
        // 获取IP信息
        $ipInfo = $this->ipInfo($ip);
        $userAgent = $request->header("user-agent");
        $browser = $this->getBrowser($userAgent);
        $device = $this->getOs($userAgent);
        $referer = $request->input("referer", $request->header("referer"));
        $result = [
            "referer_title"=> $request->input('title'),
            "referer"=> $referer,
            "location"=> $ipInfo["location"] ?? "",
            "user_agent"=> $userAgent,
            "browser"=> $browser,
            "device"=> $device,
            "ip"=> $ip,
            "time_zone"=> $ipInfo["time_zone"] ?? "Asia/Shanghai",
        ];
        return $result;
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

    /**
     * @param $ip
     * @return bool|mixed
     */
    public function ipInfo($ip) {
        if (preg_match("/^(10|172\\.(1[6-9]|2[0-9]|3[01])|192\\.168)\\./", $ip)) {
            // 本地IP
            return false;
        }
        // http://api.ipstack.com/54.146.200.9?access_key=23ad4fcb7dd3987ea85ce59b73575ba6
        $config = ApplicationContext::getContainer()->get(ConfigInterface::class);
        $url = $config->get("ip.url");
        $access_key = $config->get("ip.access_key");
        if (!$url || !$access_key) {
            return false;
        }
        $url = trim($url, '/') . "/{$ip}?access_key=" . $access_key[array_rand($access_key)];
        try {
            $data = get($url);
        } catch (Exception $e) {
            return false;
        }
        $body = $data->getBody();
        if (!$body) {
            return false;
        }
        $ipInfo =  @json_decode($body, true);
        $timezone = TimezoneMap::where("country_code", $ipInfo["country_code"] ?? "")->value("time_zone");
        return [
            "location" => $ipInfo ? "{$ipInfo["region_name"]} {$ipInfo["city"]}, {$ipInfo['country_name']}" : 'Unknown',
            "time_zone" => $timezone ?: "Asia/Shanghai",
        ];
    }

    /**
     * 获取浏览器类型
     * @param $agent
     * @return string
     */
    public function getBrowser($agent) {
        $browser     = '';
        $browser_ver = '';

        if (preg_match('/MSIE\s([^\s|;]+)/i', $agent, $regs))
        {
            $browser     = 'Internet Explorer';
            $browser_ver = $regs[1];
        }
        elseif (preg_match('/Edge[\s|\/]([^\s]+)/i', $agent, $regs))
        {
            $browser     = 'Edge';
            $browser_ver = $regs[1];
        }
        elseif (preg_match('/360SE[\s|\/]([^\s]+)/i', $agent, $regs))
        {
            $browser     = '360SE';
            $browser_ver = $regs[1];
        }
        elseif (preg_match('/MicroMessage[\s|\/]([^\s]+)/i', $agent, $regs))
        {
            $browser     = 'MicroMessage'; // 微信
            $browser_ver = $regs[1];
        }
        elseif (preg_match('/Chrome\/([^\s]+)/i', $agent, $regs))
        {
            $browser     = 'Chrome';
            $browser_ver = $regs[1];
        }
        elseif (preg_match('/FireFox\/([^\s]+)/i', $agent, $regs))
        {
            $browser     = 'FireFox';
            $browser_ver = $regs[1];
        }
        elseif (preg_match('/Maxthon\/([^\s]+)/i', $agent, $regs))
        {
            $browser     = '(Internet Explorer ' .$browser_ver. ') Maxthon'; // 傲游
            $browser_ver = '';
        }
        elseif (preg_match('/Opera[\s|\/]([^\s]+)/i', $agent, $regs))
        {
            $browser     = 'Opera';
            $browser_ver = $regs[1];
        }
        elseif (preg_match('/OmniWeb\/(v*)([^\s|;]+)/i', $agent, $regs))
        {
            $browser     = 'OmniWeb';
            $browser_ver = $regs[2];
        }
        elseif (preg_match('/Netscape([\d]*)\/([^\s]+)/i', $agent, $regs))
        {
            $browser     = 'Netscape';
            $browser_ver = $regs[2];
        }
        elseif (preg_match('/safari\/([^\s]+)/i', $agent, $regs))
        {
            $browser     = 'Safari';
            $browser_ver = $regs[1];
        }
        elseif (preg_match('/NetCaptor\s([^\s|;]+)/i', $agent, $regs))
        {
            $browser     = '(Internet Explorer ' .$browser_ver. ') NetCaptor';
            $browser_ver = $regs[1];
        }
        elseif (preg_match('/Lynx\/([^\s]+)/i', $agent, $regs))
        {
            $browser     = 'Lynx';
            $browser_ver = $regs[1];
        }

        if (!empty($browser))
        {
            return $browser . ' ' . $browser_ver;
        }
        else
        {
            return 'Unknown browser';
        }
    }

    /**
     * 获取客户端系统
     * @param $agent
     * @return string
     */
    public function getOs($agent){
        if (preg_match('/win/i', $agent) && strpos($agent, '95'))
        {
            $os = 'Windows 95';
        }
        else if (preg_match('/win 9x/i', $agent) && strpos($agent, '4.90'))
        {
            $os = 'Windows ME';
        }
        else if (preg_match('/win/i', $agent) && preg_match('/98/i', $agent))
        {
            $os = 'Windows 98';
        }
        else if (preg_match('/win/i', $agent) && preg_match('/nt 6.0/i', $agent))
        {
            $os = 'Windows Vista';
        }
        else if (preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent))
        {
            $os = 'Windows 7';
        }
        else if (preg_match('/win/i', $agent) && preg_match('/nt 6.2/i', $agent))
        {
            $os = 'Windows 8';
        }else if(preg_match('/win/i', $agent) && preg_match('/nt 10.0/i', $agent))
        {
            $os = 'Windows 10';#添加win10判断
        }else if (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent))
        {
            $os = 'Windows XP';
        }
        else if (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent))
        {
            $os = 'Windows 2000';
        }
        else if (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent))
        {
            $os = 'Windows NT';
        }
        else if (preg_match('/win/i', $agent) && preg_match('/32/i', $agent))
        {
            $os = 'Windows 32';
        }
        else if (preg_match('/linux/i', $agent))
        {
            $os = 'Linux';
        }
        else if (preg_match('/unix/i', $agent))
        {
            $os = 'Unix';
        }
        else if (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent))
        {
            $os = 'SunOS';
        }
        else if (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent))
        {
            $os = 'IBM OS/2';
        }
        else if (preg_match('/Mac/i', $agent) && preg_match('/PC/i', $agent))
        {
            $os = 'Macintosh';
        }
        else if (preg_match('/Mac/i', $agent))
        {
            $os = 'Mac';
        }
        else if (preg_match('/PowerPC/i', $agent))
        {
            $os = 'PowerPC';
        }
        else if (preg_match('/AIX/i', $agent))
        {
            $os = 'AIX';
        }
        else if (preg_match('/HPUX/i', $agent))
        {
            $os = 'HPUX';
        }
        else if (preg_match('/NetBSD/i', $agent))
        {
            $os = 'NetBSD';
        }
        else if (preg_match('/BSD/i', $agent))
        {
            $os = 'BSD';
        }
        else if (preg_match('/OSF1/i', $agent))
        {
            $os = 'OSF1';
        }
        else if (preg_match('/IRIX/i', $agent))
        {
            $os = 'IRIX';
        }
        else if (preg_match('/FreeBSD/i', $agent))
        {
            $os = 'FreeBSD';
        }
        else if (preg_match('/teleport/i', $agent))
        {
            $os = 'teleport';
        }
        else if (preg_match('/flashget/i', $agent))
        {
            $os = 'flashget';
        }
        else if (preg_match('/webzip/i', $agent))
        {
            $os = 'webzip';
        }
        else if (preg_match('/offline/i', $agent))
        {
            $os = 'offline';
        }
        else
        {
            $os = 'Unknown system';
        }
        return $os;
    }
}