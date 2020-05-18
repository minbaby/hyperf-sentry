<?php

namespace Minbaby\HyperfSentry;

use Hyperf\Contract\ConfigInterface;
use Minbaby\HyperfSentry\Integration\Integration;
use Psr\Container\ContainerInterface;
use Sentry\ClientBuilder;
use Sentry\Integration as SdkIntegration;
use Sentry\State\Hub;

class HubFactory
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke()
    {
        /**@var ClientBuilder $clientBuilder */
        $clientBuilder = $this->container->get(ClientBuilder::class);

        $options = $clientBuilder->getOptions();

        $userIntegrations = $this->resolveIntegrationsFromUserConfig();

        $options->setIntegrations(static function (array $integrations) use ($options, $userIntegrations) {
            $allIntegrations = array_merge($integrations, $userIntegrations);

            if (!$options->hasDefaultIntegrations()) {
                return $allIntegrations;
            }

            // Remove the default error and fatal exception listeners to let this handle those
            // itself. These event are still bubbling up through the documented changes in the users
            // `ExceptionHandler` of their application or through the log channel integration to Sentry
            return array_filter($allIntegrations, static function (SdkIntegration\IntegrationInterface $integration): bool {
                if ($integration instanceof SdkIntegration\ErrorListenerIntegration) {
                    return false;
                }

                if ($integration instanceof SdkIntegration\ExceptionListenerIntegration) {
                    return false;
                }

                if ($integration instanceof SdkIntegration\FatalErrorListenerIntegration) {
                    return false;
                }

                return true;
            });
        });

        $hub = new Hub($clientBuilder->getClient());

        return $hub;
    }

    /**
     * Resolve the integrations from the user configuration with the container.
     *
     * @return array
     */
    protected function resolveIntegrationsFromUserConfig(): array
    {
        $config = $this->container->get(ConfigInterface::class);

        $integrations = [new Integration()];

        $userIntegrations = $config->get('sentry.integrations', []);

        foreach ($userIntegrations as $userIntegration) {
            if ($userIntegration instanceof SdkIntegration\IntegrationInterface) {
                $integrations[] = $userIntegration;
            } elseif (\is_string($userIntegration)) {
                $resolvedIntegration = $this->container->get($userIntegration);

                if (!($resolvedIntegration instanceof SdkIntegration\IntegrationInterface)) {
                    throw new \RuntimeException('Sentry integrations should a instance of `\Sentry\Integration\IntegrationInterface`.');
                }

                $integrations[] = $resolvedIntegration;
            } else {
                throw new \RuntimeException('Sentry integrations should either be a container reference or a instance of `\Sentry\Integration\IntegrationInterface`.');
            }
        }

        return $integrations;
    }
}