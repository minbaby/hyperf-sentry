<?php

declare(strict_types=1);

namespace Minbaby\HyperfSentry;

use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Sentry\ClientBuilderInterface;
use Sentry\State\HubInterface;

final class SentryContext
{
    const SENTRY_HUB = 'hyperf.sentry_hub';

    public static function getHub(): HubInterface
    {
        if (! Context::has(static::SENTRY_HUB)) {
            $hub = self::getHubInstance();
            $hub->bindClient(self::getClientBuilder()->getClient());
            Context::set(static::SENTRY_HUB, $hub);
        }

        return Context::get(static::SENTRY_HUB);
    }

    private static function getHubInstance(): HubInterface
    {
        return make(HubInterface::class);
    }

    private static function getClientBuilder(): ClientBuilderInterface
    {
        if (! ApplicationContext::hasContainer()) {
            throw new \RuntimeException('no container');
        }
        return ApplicationContext::getContainer()->get(ClientBuilderInterface::class);
    }
}
