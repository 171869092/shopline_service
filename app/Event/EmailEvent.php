<?php
declare(strict_types=1);
namespace App\Event;
use Hyperf\Config\Annotation\Value;

class EmailEvent{
    /**
     * @var string 发送方邮箱
     */
    public $senEmail;

    /**
     * @var string 发送方名字
     */
    public $senUser;

    /**
     * @var string 发送邮件标题
     */
    public $title;

    /**
     * @var string 发送内容
     */
    public $html;

    /**
     * @var string 收件人邮箱
     */
    public $email;

    /**
     * @var array 配置
     */
    public $option;

    /**
     * @Value("aws.smtp.host")
     */
    public $host;

    /**
     * @Value("aws.smtp.port")
     */
    public $port;

    /**
     * @Value("aws.smtp.username")
     */
    public $username;

    /**
     * @Value("aws.smtp.password")
     */
    public $password;

    /**
     * @Value("aws.smtp.set_form")
     */
    public $setForm;

    /**
     * @Value("aws.smtp.set_name")
     */
    public $setName;

    /**
     * @Value("aws.smtp.charset")
     */
    public $charset;

    /**
     * @Value("aws.smtp.secure")
     */
    public $secure;

    public function __construct($email,$title,$html,$senEmail=null,$senUser=null)
    {
        $this->email = $email;
        $this->senUser = $senUser;
        $this->senEmail = $senEmail;
        $this->title = $title;
        $this->html = $html;
        $this->option = [
            'charset' => $this->charset,
            'host' => $this->host,
            'port' => $this->port,
            'username' => $this->username,
            'password' => $this->password,
            'setForm' => $this->setForm,
            'setName' => $this->setName,
            'secure' => $this->secure,
        ];
    }
}
