<?php


namespace App\Service\Dashboard;

use App\Collector\SocketUserCollector;
use App\Model\GuestTrace;
use App\Model\Recommend;
use App\Model\Store;
use Hyperf\DbConnection\Db;

class DashboardService
{
    /**
     * @param $user_id
     * @return array
     */
    public function getDashboardData($user_id){
        $result = [
            'live_view'=>[
                'today_visitors'=>0,//今日访客
                'online_visitors'=>0,//当前在线访客
                'chat_visitors'=>0,//今日对话 今日咨询过的访客数量
                'old_visitors'=>0,//历史对话 不包含今日对话
            ],
            'track'=>[],
            'recommend'=>[]
        ];
        $result['recommend'] = Recommend::get()->toArray();
        $result['live_view'] = [
            'today_visitors'=> GuestTrace::where(['user_id'=>$user_id,'total_date'=>date('Y-m-d')])->count(),//今日访客
            'online_visitors'=>SocketUserCollector::getOnlineGuestCount((string)$user_id),//当前在线访客
            'chat_visitors'=>GuestTrace::where(['user_id'=>$user_id,'total_date'=>date('Y-m-d'),'is_chat'=>1])->count(),
            'old_visitors'=>GuestTrace::whereRaw(" user_id=$user_id and total_date !=". date('Y-m-d') ." and is_chat=1 ")->count(),
        ];
        $store = Store::where(['user_id'=>$user_id])->get();
        $store_arr = [];
        foreach ($store as $k=>$v){
            for ($i = 29; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i} day"));
                $store_arr[$v['store_url']][$date] = 0;
            }
        }
        $old_day = date('Y-m-d', strtotime("-30 day"));
        $recent = Db::select("select store_url,total_date ,count(id) as count  from guest_trace where user_id=1 and total_date>'$old_day' group by total_date,store_url");
        foreach ($recent as $k=>$v){
            $store_arr[$v->store_url][$v->total_date] = $v->count;
        }
        $result['track'] = $store_arr;
        return $result;
    }

    /**
     * 获取一个日期范围内的日期
     * @param $interval
     * @param $type: - 过去的日期   + 未来的日期
     * @return array
     */
    public function getDateInterval($interval,$type)
    {
        $dateArr = [];
        for ($i = $interval - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("{$type}{$i} day"));
            array_push($dateArr, [$date=>0]);
        }
        if($type=='+')$dateArr=array_reverse($dateArr);
        return $dateArr;
    }
}