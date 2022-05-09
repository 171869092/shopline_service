<?php

namespace App\Crontab;

/**
 * Class EnableChecker
 * @package App\Crontab
 */
class EnableChecker
{
    /**
     * 定时任务的开关（线上开启）
     * @return bool
     */
    public function isOnlineEnable(): bool
    {
        if (config("app_env") == "dev") {
            return  true;
        }
        return false;
    }
}
