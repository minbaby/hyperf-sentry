<?php

namespace Minbaby\HyperfSentry;

use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Hyperf\Utils\Traits\StaticInstance;
use Sentry\ClientBuilderInterface;
use Sentry\State\HubInterface;

final class SentryContext
{
    const SENTRY_HUB = 'hyperf.sentry_hub';

    public static function getHub(): HubInterface
    {
        if (!Context::has(static::SENTRY_HUB)) {
            $hub = self::getHubInstance();
            $hub->bindClient(self::getClientBuilder()->getClient());
            Context::set(static::SENTRY_HUB, $hub);
        }

        return Context::get(static::SENTRY_HUB);
    }

    protected static function getHubInstance(): HubInterface
    {
        return make(HubInterface::class);
    }

    /**
     * @return ClientBuilderInterface
     */
    protected static function getClientBuilder(): ClientBuilderInterface
    {
        if (!ApplicationContext::hasContainer()) {
            throw new \RuntimeException('no container');
        }
        return ApplicationContext::getContainer()->get(ClientBuilderInterface::class);
    }
}