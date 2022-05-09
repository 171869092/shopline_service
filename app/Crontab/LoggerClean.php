<?php


namespace App\Crontab;

use Hyperf\Crontab\Annotation\Crontab;

/**
 * Class LoggerClean
 * @package App\Crontab
 */
class LoggerClean
{
    /**
     * 每天清理一下日志
     * @Crontab(rule="0 0 * * *", memo="清理日志", name="LoggerClean_start", enable=true)
     */
    public function start() {
        $logs = BASE_PATH . '/runtime/logs/';
        if (is_dir($logs)) {
            $pathList = scandir($logs);
            foreach ($pathList as $path) {
                if ($path != "." && $path != "..") {
                    $modify = filemtime($logs . $path);
                    if ($modify && $modify <= (time() - (86400 * 10))) {
                        unlink($logs . $path);
                    }
                }
            }
        }
    }
}