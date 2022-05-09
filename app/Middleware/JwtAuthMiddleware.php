<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Annotation\NotAuth;
use FastRoute\Dispatcher;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\HttpServer\Router\Dispatched;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qbhy\HyperfAuth\AuthMiddleware;

class JwtAuthMiddleware extends AuthMiddleware
{
    protected $guards = ['jwt']; // 支持多个 guard

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Dispatched $dispatched */
        $dispatched = $request->getAttribute(Dispatched::class);
        if ($dispatched instanceof Dispatched && $this->shouldHandle($dispatched)) {
            [$class, $method] = $this->prepareHandler($dispatched->handler->callback);
            if (!$this->isNotAuth($class, $method)) {
                return parent::process($request, $handler);
            }
        }
        return $handler->handle($request);
    }

    /**
     * @param Dispatched $dispatched
     * @return bool
     */
    protected function shouldHandle(Dispatched $dispatched): bool
    {
        return $dispatched->status === Dispatcher::FOUND && ! $dispatched->handler->callback instanceof \Closure;
    }

    /**
     * @see \Hyperf\HttpServer\CoreMiddleware::prepareHandler()
     * @param array|string $handler
     */
    protected function prepareHandler($handler): array
    {
        if (is_string($handler)) {
            if (strpos($handler, '@') !== false) {
                return explode('@', $handler);
            }
            $array = explode('::', $handler);
            return [$array[0], $array[1] ?? null];
        }
        if (is_array($handler) && isset($handler[0], $handler[1])) {
            return $handler;
        }
        throw new \RuntimeException('Handler not exist.');
    }

    /**
     * 是否需要跳过验证
     * @param $class
     * @param $method
     * @return bool
     */
    protected function isNotAuth(string $class, string $method):bool {
        if (AnnotationCollector::getClassAnnotation($class, NotAuth::class)) {
            return true;
        }
        $annotation = AnnotationCollector::getClassMethodAnnotation($class, $method);
        if ($annotation) {
            foreach ($annotation as $value) {
                if ($value instanceof NotAuth) return true;
            }
        }
        return false;
    }
}
