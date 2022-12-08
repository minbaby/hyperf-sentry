<?php

declare(strict_types=1);

namespace Minbaby\HyperfSentry;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Server\ServerManager;
use Minbaby\HyperfSentry\Integration\RequestFetcher;
use Psr\Container\ContainerInterface;
use Sentry\ClientBuilder;
use Sentry\ClientBuilderInterface;
use Sentry\Integration\RequestIntegration;
use Sentry\SentrySdk;
use Sentry\State\Hub;
use Sentry\State\HubInterface;

class BootApplicationListener implements ListenerInterface
{
    /**
     * @var \Hyperf\Di\Container|\Psr\Container\ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     * @param AfterWorkerStart|object $event
     */
    public function process(object $event): void
    {
        $this->configureAndRegisterClient();
    }

    protected function configureAndRegisterClient()
    {
        $this->container->define(ClientBuilderInterface::class, function () {
            $userConfig = $this->getUserConfig();

            $basePath = defined('\BASE_PATH') ? \BASE_PATH : '';

            unset($userConfig['breadcrumbs']);

            $fetcher = null;
            if (ServerManager::list()) {
                $fetcher = $this->container->get(RequestFetcher::class);
            }
            $options = array_merge(
                [
                    'prefixes' => [$basePath],
                    'in_app_exclude' => [$basePath . '/vendor'],
                    'integrations' => [
                        new RequestIntegration($fetcher),
                    ],
                ],
                $userConfig
            );

            $clientBuilder = ClientBuilder::create($options);

            $clientBuilder->setSdkIdentifier(Version::SDK_IDENTIFIER);
            $clientBuilder->setSdkVersion(Version::SDK_VERSION);
            $clientBuilder->setLogger($this->container->get(LoggerFactory::class)->get('sentry'));

            return $clientBuilder;
        });

        $this->container->define(HubInterface::class, function () {
            $clientBuilder = $this->container->get(ClientBuilderInterface::class);

            $hub = new Hub($clientBuilder->getClient());
            SentrySdk::setCurrentHub($hub);

            return $hub;
        });
    }

    protected function getUserConfig(): array
    {
        return $this->container->get(ConfigInterface::class)->get('sentry', []);
    }
}
