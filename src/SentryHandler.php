<?php

namespace Minbaby\HyperfSentry;

use Hyperf\Contract\ConfigInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Sentry\ClientBuilder;
use Sentry\FlushableClientInterface;
use Sentry\State\Scope;
use \Throwable;
use function Sentry\configureScope;

class SentryHandler extends ExceptionHandler
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->log = $this->container->get(LoggerFactory::class)->get('sentry');
        $this->log->info('init');
    }

    /**
     * Handle the exception, and return the specified result.
     * @param Throwable $throwable
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->log->info('handle');
        $config = $this->container->get(ConfigInterface::class);


        SentryContext::getHub()->configureScope(function (Scope $scope) {
            $scope->setUser(['email' => 'minbaby@m.com']);
        });

        SentryContext::getHub()->captureException($throwable);
        $this->log->info('captureException');

        $clientBuilder = $this->container->get(ClientBuilder::class);

        if (($client = $clientBuilder->getClient()) instanceof FlushableClientInterface) {
            $this->log->info('flush');
            $client->flush((int) $config->get('sentry.flush_timeout', 2));
        }

        $this->log->info('return');
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