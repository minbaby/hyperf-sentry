<?php

declare(strict_types=1);

namespace Minbaby\HyperfSentry;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Events\ConnectionEvent;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Database\Events\TransactionBeginning;
use Hyperf\Database\Events\TransactionCommitted;
use Hyperf\Database\Events\TransactionRolledBack;
use Hyperf\Event\ListenerProvider;
use Minbaby\HyperfSentry\Integration\Integration;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Sentry\Breadcrumb;

class EventHandler
{
    protected static $eventHandlerMap = [
        QueryExecuted::class => 'queryExecuted',
        TransactionBeginning::class => 'transaction',
        TransactionCommitted::class => 'transaction',
        TransactionRolledBack::class => 'transaction',
    ];

    protected $container;

    /** @var ListenerProvider */
    protected $event;

    /**
     * @var array
     */
    protected $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->event = $this->container->get(ListenerProviderInterface::class);
        $this->config = $this->container->get(ConfigInterface::class)->get('sentry', []);
    }

    /**
     * Pass through the event and capture any errors.
     *
     * @param string $method
     * @param array $arguments
     */
    public function __call($method, $arguments)
    {
        $handlerMethod = $handlerMethod = "{$method}Handler";

        if (! method_exists($this, $handlerMethod)) {
            throw new \RuntimeException("Missing event handler: {$handlerMethod}");
        }

        try {
            call_user_func_array([$this, $handlerMethod], $arguments);
        } catch (\Exception $exception) {
            // Ignore
        }
    }

    public function subscribe()
    {
        foreach (self::$eventHandlerMap as $event => $handler) {
            $this->event->on($event, [$this, $handler]);
        }
    }

    public function subscribeQueueEvents()
    {
    }

    /**
     * @param ConnectionEvent|object $event
     */
    protected function transactionHandler(object $event)
    {
        $data = [
            'connectionName' => $event->connectionName,
        ];

        Integration::addBreadcrumb(new Breadcrumb(
            Breadcrumb::LEVEL_INFO,
            Breadcrumb::TYPE_DEFAULT,
            'sql.query',
            get_class($event),
            $data
        ));
    }

    /**
     * @param object|QueryExecuted $event
     */
    protected function queryExecutedHandler(object $event)
    {
        if (! data_get($this->config, 'breadcrumbs.sql_queries', false)) {
            return;
        }
        $data = ['connectionName' => $event->connectionName];

        if ($event->time !== null) {
            $data['executionTimeMs'] = $event->time;
        }

        if (data_get($this->config, 'breadcrumbs.sql_bindings', false)) {
            $data['bindings'] = $event->bindings;
        }

        Integration::addBreadcrumb(new Breadcrumb(
            Breadcrumb::LEVEL_INFO,
            Breadcrumb::TYPE_DEFAULT,
            'sql.query',
            $event->sql,
            $data
        ));
    }
}
