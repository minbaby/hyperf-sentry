<?php

namespace Minbaby\HyperfSentry\Aspect;

use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Utils\Context;

/**
 * Hook 不安全的单例实现
 */
class SingletonHookAspect extends AbstractAspect
{
    public $classes = [
        \Sentry\EventType::class,
        \Sentry\ResponseStatus::class,
        \Sentry\Integration\IntegrationRegistry::class,
        \Sentry\State\HubAdapter::class,
        \Sentry\Tracing\SpanStatus::class,
    ];

    /**
     * @param \Hyperf\Di\Aop\ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed
     * @throws \Hyperf\Di\Exception\Exception
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if ($proceedingJoinPoint->methodName == 'getInstance') {
            $key = $proceedingJoinPoint->className;
            $args = $proceedingJoinPoint->getArguments();
            if (!empty($args)) {
                $key .= $args[0];
            }
            if (!Context::has($key)) {
                Context::set($key, $proceedingJoinPoint->process());
            }

            return Context::get($key);
        }
        return $proceedingJoinPoint->process();
    }
}
