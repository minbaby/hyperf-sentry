<?php

namespace Minbaby\HyperfSentry;

use Hyperf\Utils\Context;
use Sentry\State\HubInterface;

final class SentryContext 
{
    const SENTRY_HUB = 'hyperf.sentry_hub';

    public static function getHub(): HubInterface
    {
        if (!Context::has(static::SENTRY_HUB)) {
            //TODO: event

            Context::set(static::SENTRY_HUB, make(HubInterface::class));
        }
        
        return Context::get(static::SENTRY_HUB);
    }
}