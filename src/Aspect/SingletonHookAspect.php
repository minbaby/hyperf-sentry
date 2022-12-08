<?php

declare(strict_types=1);

namespace Minbaby\HyperfSentry\Aspect;

use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Context\Context;

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
     * @throws \Hyperf\Di\Exception\Exception
     * @return mixed
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
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
