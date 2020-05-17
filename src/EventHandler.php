<?php

namespace Minbaby\HyperfSentry;

use Hyperf\Database\Events\ConnectionEvent;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Database\Events\TransactionBeginning;
use Hyperf\Database\Events\TransactionCommitted;
use Hyperf\Database\Events\TransactionRolledBack;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\ListenerProvider;
use Hyperf\Logger\LoggerFactory;
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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->event = $this->container->get(ListenerProviderInterface::class);
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
     * Pass through the event and capture any errors.
     *
     * @param string $method
     * @param array $arguments
     */
    public function __call($method, $arguments)
    {
        $handlerMethod = $handlerMethod = "{$method}Handler";

        if (!method_exists($this, $handlerMethod)) {
            throw new \RuntimeException("Missing event handler: {$handlerMethod}");
        }

        try {
            call_user_func_array([$this, $handlerMethod], $arguments);
        } catch (Exception $exception) {
            // Ignore
        }
    }

    /**
     * @param object|ConnectionEvent $event
     */
    protected function transactionHandler(object $event)
    {

        $data = [
            'connectionName' => $event->connectionName
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
        $data = ['connectionName' => $event->connectionName];

        if ($event->time !== null) {
            $data['executionTimeMs'] = $event->time;
        }

        //TODO
//        if ($this->recordSqlBindings) {
            $data['bindings'] = $event->bindings;
//        }


        Integration::addBreadcrumb(new Breadcrumb(
            Breadcrumb::LEVEL_INFO,
            Breadcrumb::TYPE_DEFAULT,
            'sql.query',
            $event->sql,
            $data
        ));
    }
}