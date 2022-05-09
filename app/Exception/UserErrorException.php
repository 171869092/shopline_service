<?php


namespace App\Exception;


use App\Constants\ErrorCode;

/**
 * 用户自定义异常抛出
 * Class UserException
 * @package App\Exception
 */
class UserErrorException extends \RuntimeException
{
    /**
     * UserErrorException constructor.
     * @param int $code
     * @param null|string|array $message
     * @param \Throwable|null $previous
     */
    public function __construct(int $code = 0, $message = null, \Throwable $previous = null)
    {
        if (is_null($message) || is_array($message)) {
            $message = is_array($message) ? ErrorCode::getMessage($code, $message) : ErrorCode::getMessage($code) ;
        }

        parent::__construct($message, $code, $previous);
    }
}