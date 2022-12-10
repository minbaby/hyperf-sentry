<?php

declare(strict_types=1);

namespace Minbaby\HyperfSentry\Aspect;

use Hyperf\Context\Context;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;

/**
 * Hook 不安全的单例实现.
 */
class SingletonHookAspect extends AbstractAspect
{
    public array $classes = [
        \Sentry\EventType::class,
        \Sentry\ResponseStatus::class,
        \Sentry\Integration\IntegrationRegistry::class,
        \Sentry\State\HubAdapter::class,
        \Sentry\Tracing\SpanStatus::class,
    ];

    /**
     * @throws Exception
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint): mixed
    {
        if ($proceedingJoinPoint->methodName == 'getInstance') {
            $key = $proceedingJoinPoint->className;
            $args = $proceedingJoinPoint->getArguments();
            if (! empty($args)) {
                $key .= $args[0];
            }
            if (! Context::has($key)) {
                Context::set($key, $proceedingJoinPoint->process());
            }

            return Context::get($key);
        }
        return $proceedingJoinPoint->process();
    }
}
