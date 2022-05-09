<?php
declare(strict_types=1);
namespace App\Listener;

use App\Event\EmailEvent;
use Hyperf\Event\Contract\ListenerInterface;
use PHPMailer\PHPMailer\PHPMailer;
use Hyperf\Event\Annotation\Listener;

/**
 * Class EmailListener
 * @Listener()
 * @package App\Listener
 */
class EmailListener implements ListenerInterface{
    public function listen(): array
    {
        // TODO: Implement listen() method.
        return [
            EmailEvent::class
        ];
    }

    public function process(object $event)
    {
        // TODO: Implement process() method.
        try {
            go(function () use ($event){
                $mail = new PHPMailer();
                $mail->CharSet = $event->option['charset']; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
                $mail->IsSMTP(); // 设定使用SMTP服务
                $mail->SMTPDebug = 2; // SMTP调试功能
                $mail->SMTPAuth   = true;
                $mail->SMTPSecure = $event->option['secure'];
                $mail->isHTML(true);
                $mail->Host = $event->option['host'];
                $mail->Port = $event->option['port']; // SMTP服务器的端口号
                $mail->Username = $event->option['username']; // SMTP服务器用户名
                $mail->Password = $event->option['password']; // SMTP服务器密码c
                $mail->setFrom($event->senEmail ?? $event->option['setForm'], $event->senUser ?? $event->option['setName']);
                $mail->Subject = $event->title; //. title
                $mail->MsgHTML($event->html); //. msg
                $mail->AddAddress($event->email); // 收件人
                try {
                    $result = $mail->send();
                    if (!$result){
                        return $mail->ErrorInfo;
                    }
                    return true;
                }catch (\Exception $exception){
                    throw new \Exception($mail->ErrorInfo);
                }
            });
        }catch (\Throwable $e){
            print_r($e->getMessage());
        }
    }
}
