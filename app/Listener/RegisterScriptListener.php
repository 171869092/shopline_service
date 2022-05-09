<?php
declare(strict_types=1);
namespace App\Listener;
use App\Event\RegisterScriptEvent;
use App\Model\ScriptTag;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Event\Annotation\Listener;

/**
 * @Listener()
 * Class RegisterScriptListener
 * @package App\Listener
 */
class RegisterScriptListener implements ListenerInterface{
    public function listen(): array
    {
        // TODO: Implement listen() method.
        return [
            RegisterScriptEvent::class
        ];
    }

    public function process(object $event)
    {
        // TODO: Implement process() method.
        try {
            $script = ScriptTag::where(['store_url' => $event->storeUrl]);
            if ($script->exists()){
                # 先不更新
                return;
            }
            $result = $event->shopify->ScriptTag->post([
                "event" => "onload",
                "src" => $event->host . $event->script
            ]);
            if (!$result){
                $event->logger->info('register error');
            }
            $event->logger->info('register success');
            ScriptTag::create([
                'script_id' => $result['id'],
                'src' => $result['src'],
                'display_scope' => $result['display_scope'],
                'cache' => $result['cache'],
                'store_url' => $event->storeUrl,
                'create_time' => date('Y-m-d H:i:s'),
            ]);
        }catch (\Exception $e){
            $event->logger->info('register error: '. $e->getMessage());
        }
    }
}
