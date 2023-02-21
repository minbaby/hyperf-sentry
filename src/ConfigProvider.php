<?php

declare(strict_types=1);

namespace Minbaby\HyperfSentry;

use Minbaby\HyperfSentry\Aspect\SingletonHookAspect;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'commands' => [
                TestCommand::class,
            ],
            'listeners' => [
                AfterWorkerStartListener::class,
                BootApplicationListener::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                    'class_map' => [
                        \Sentry\SentrySdk::class => BASE_PATH . '/vendor/minbaby/hyperf-sentry/src/class_map/SentrySdk.php',
                    ],
                ],
            ],
            'aspects' => [
                SingletonHookAspect::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for tracer.',
                    'source' => __DIR__ . '/../publish/sentry.php',
                    'destination' => BASE_PATH . '/config/autoload/sentry.php',
                ],
            ],
        ];
    }
}
