<?php

declare(strict_types=1);

namespace Minbaby\HyperfSentry;

use Hyperf\Contract\ConfigInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Sentry\ClientBuilderInterface;
use Sentry\ClientInterface;
use Sentry\SentrySdk;
use Sentry\State\HubInterface;
use Throwable;

class SentryExceptionHandler extends ExceptionHandler
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Handle the exception, and return the specified result.
     * @param \Throwable $throwable
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return ResponseInterface
     */
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        $hub = $this->container->get(HubInterface::class);
        $hub->captureException($throwable);

        $hub->getClient()->flush();

        return $response;
    }

    /**
     * Determine if the current exception handler should handle the exception,.
     *
     * @return bool
     *              If return true, then this exception handler will handle the exception,
     *              If return false, then delegate to next handler
     */
    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
