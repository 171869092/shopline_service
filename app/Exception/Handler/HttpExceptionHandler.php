<?php


namespace App\Exception\Handler;


use App\Constants\ErrorCode;
use Exception;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Qbhy\HyperfAuth\Exception\UnauthorizedException;
use Qbhy\SimpleJwt\Exceptions\InvalidTokenException;
use Qbhy\SimpleJwt\Exceptions\SignatureException;
use Throwable;

class HttpExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $result = [
            "code"=> $throwable->getCode() ?: 400,
            "msg"=> $throwable->getMessage(),
            "data"=> [],
        ];
        if ($throwable instanceof UnauthorizedException ||$throwable instanceof InvalidTokenException || $throwable instanceof SignatureException) {
            // token校验失败
            $result["code"] = ErrorCode::TOKEN_ERROR;
        }
        return $response->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus(200)
            ->withBody(new SwooleStream(json_encode($result)));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof Exception;
    }
}