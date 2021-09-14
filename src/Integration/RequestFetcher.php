<?php

declare(strict_types=1);

namespace Minbaby\HyperfSentry\Integration;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sentry\Integration\RequestFetcherInterface;

class RequestFetcher implements RequestFetcherInterface
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function fetchRequest(): ?ServerRequestInterface
    {
        return $this->container->get(ServerRequestInterface::class);
    }
}
