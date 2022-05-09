<?php


namespace App\Service\Settings;


use App\Model\Setting;
use Hyperf\DbConnection\Db;

class SettingsService
{
    public function getSettings($user_id,$setting_type){
        $setting = Setting::where(['user_id'=>$user_id,'setting_type'=>$setting_type])->first();
        if($setting){
            return json_decode($setting->toArray()['content'],true);
        }
        return [];
    }

    public function modifySettings($user_id,$params){
        $key = trim($params['key']);
        $value = trim($params['value']);
        $setting_type = trim($params['type']);
        $time = date('Y-m-d H:i:s');
        $sql = "UPDATE settings SET content = json_set(content, '$.$key', '$value'),update_time='$time' WHERE user_id = $user_id  and setting_type= $setting_type";
        $up = Db::update($sql);
        if(!$up){
            //创建一天新的配置记录
            Setting::create(['user_id'=>$user_id,'setting_type'=>$setting_type,'content'=>json_encode([$key=>$value])]);
        }
        return [];
    }

}