<?php

declare(strict_types=1);

namespace Minbaby\HyperfSentry;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use Psr\Container\ContainerInterface;

class AfterWorkerStartListener implements ListenerInterface
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
            AfterWorkerStart::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     * @param AfterWorkerStart|object $event
     */
    public function process(object $event)
    {
        $eventHandler = $this->container->get(EventHandler::class);
        $eventHandler->subscribe();
    }
}
