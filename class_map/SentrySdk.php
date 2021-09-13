<?php

namespace Sentry;

use Hyperf\Utils\Context;
use Sentry\State\Hub;
use Sentry\State\HubInterface;

/**
 * @see \Sentry\SentrySdk
 */
class SentrySdk
{
    /**
     * @var HubInterface|null The current hub
     */
    private static $currentHub;

    /**
     * Constructor.
     */
    private function __construct()
    {
    }

    /**
     * Initializes the SDK by creating a new hub instance each time this method
     * gets called.
     */
    public static function init(): HubInterface
    {
        echo __CLASS__, PHP_EOL;
        Context::set(__CLASS__, new Hub());

        return Context::get(__CLASS__);
    }

    /**
     * Gets the current hub. If it's not initialized then creates a new instance
     * and sets it as current hub.
     */
    public static function getCurrentHub(): HubInterface
    {
        if (!Context::has(__CLASS__)) {
            Context::set(__CLASS__, new Hub());
        }

        return Context::get(__CLASS__);
    }

    /**
     * Sets the current hub.
     *
     * @param HubInterface $hub The hub to set
     */
    public static function setCurrentHub(HubInterface $hub): HubInterface
    {
        Context::set(__CLASS__, $hub);

        return $hub;
    }
}
