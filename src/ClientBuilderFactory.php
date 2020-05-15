<?php

namespace Minbaby\HyperfSentry;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Sentry\ClientBuilder;

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

        $options = array_merge(
            [
                'prefixes' => [\BASE_PATH],
                'in_app_exclude' => [\BASE_PATH . "/vendor"],
            ],
            $userConfig
        );


        $clientBuilder = ClientBuilder::create($options);

        $clientBuilder->setSdkVersion(Version::SDK_VERSION);
        $clientBuilder->setSdkIdentifier(Version::SDK_IDENTIFIER);

        return $clientBuilder;
    }
}