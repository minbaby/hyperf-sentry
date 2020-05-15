<?php
namespace Minbaby\HyperfSentry;

use Sentry\ClientBuilderInterface;
use Sentry\State\HubInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                HubInterface::class => HubFactory::class,
                ClientBuilderInterface::class => ClientBuilderFactory::class,
            ],
            'commands' => [
            ],
            'listeners' => [
            ],
            'scan' => [
                'paths' => [
                    __DIR__,
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for tracer.',
                    'source' => __DIR__ . '/../publish/opentracing.php',
                    'destination' => BASE_PATH . '/config/autoload/opentracing.php',
                ],
            ],
        ];
    }
}