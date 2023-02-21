<?php

declare(strict_types=1);

namespace Minbaby\HyperfSentry;

use Exception;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Sentry\ClientBuilder;
use Sentry\State\Hub;
use Sentry\State\HubInterface;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\TransactionContext;
use Symfony\Component\Console\Input\InputOption;

/**
 * @Command
 */
class TestCommand extends HyperfCommand
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('sentry:test');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Generate a test event and send it to Sentry');
        $this->addOption('transaction', null, InputOption::VALUE_OPTIONAL, 'transaction');
        $this->addOption('dsn', null, InputOption::VALUE_OPTIONAL, 'dsn');
    }

    public function handle()
    {
        try {
            $hub = $this->container->get(HubInterface::class);

            if ($this->option('dsn')) {
                $hub = new Hub(ClientBuilder::create(['dsn' => $this->option('dsn')])->getClient());
            }

            if ($hub->getClient()->getOptions()->getDsn()) {
                $this->info('[Sentry] DSN discovered!');
            } else {
                $this->error('[Sentry] Could not discover DSN!');
                $this->error('[Sentry] Please check if your DSN is set properly in your config or `.env` as `SENTRY_DSN`.');

                return;
            }

            if ($this->option('transaction')) {
                $hub->getClient()->getOptions()->setTracesSampleRate(1);
            }

            $transactionContext = new TransactionContext();
            $transactionContext->setName('Sentry Test Transaction');
            $transactionContext->setOp('sentry.test');
            $transaction = $hub->startTransaction($transactionContext);

            $spanContext = new SpanContext();
            $spanContext->setOp('sentry.sent');
            $span1 = $transaction->startChild($spanContext);

            $this->info('[Sentry] Generating test Event');

            $ex = $this->generateTestException('command name', ['foo' => 'bar']);

            $eventId = $hub->captureException($ex);

            $this->info('[Sentry] Sending test Event');

            $span1->finish();
            $result = $transaction->finish();
            if ($result) {
                $this->info("[Sentry] Transaction sent: {$result}");
            }

            if (! $eventId) {
                $this->error('[Sentry] There was an error sending the test event.');
                $this->error('[Sentry] Please check if your DSN is set properly in your config or `.env` as `SENTRY_DSN`.');
            } else {
                $this->info("[Sentry] Event sent with ID: {$eventId}");
            }
        } catch (Exception $e) {
            $this->error("[Sentry] {$e->getMessage()}");
        }
    }

    protected function option($name)
    {
        return $this->input->getOption($name);
    }

    /**
     * Generate a test exception to send to Sentry.
     * @param mixed $command
     * @param mixed $arg
     */
    protected function generateTestException($command, $arg): ?Exception
    {
        // Do something silly
        try {
            throw new Exception('This is a test exception sent from the Sentry Hyperf SDK.');
        } catch (Exception $ex) {
            return $ex;
        }
    }
}
