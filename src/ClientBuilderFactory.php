<?php

declare(strict_types=1);

namespace Minbaby\HyperfSentry;

use Hyperf\Contract\ConfigInterface;
use Minbaby\HyperfSentry\Integration\RequestIntegration;
use Psr\Container\ContainerInterface;
use Sentry\ClientBuilder;
use Sentry\ClientBuilderInterface;

/**
 * Class ClientBuilderFactory.
 * @see ClientBuilderInterface
 */
class ClientBuilderFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);

        $userConfig = $config->get('sentry', []);

        unset(
            // We do not want this setting to hit our main client because it's Laravel specific
            $userConfig['breadcrumbs'],
            // We resolve the integrations through the container later, so we initially do not pass it to the SDK yet
            $userConfig['integrations'],
            // This is kept for backwards compatibility and can be dropped in a future breaking release
            $userConfig['breadcrumbs.sql_bindings']
        );

        $basePath = defined('\BASE_PATH') ? \BASE_PATH : '';

        $options = array_merge(
            [
                'default_integrations' => false,
                'integrations' => [
                    new RequestIntegration(),
                ],
                'prefixes' => [$basePath],
                'in_app_exclude' => [$basePath . '/vendor'],
            ],
            $userConfig
        );

        $clientBuilder = ClientBuilder::create($options);
        $clientBuilder->setSdkVersion(Version::SDK_VERSION);
        $clientBuilder->setSdkIdentifier(Version::SDK_IDENTIFIER);

        return $clientBuilder;
    }
}
