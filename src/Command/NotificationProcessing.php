<?php declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;
use App\Service\NotificationService;

/**
 * Scrip should be run by cron for every time period - for example by every 1 minutes
 * Is responsible for processing all unsent notifications - sending to RabbitMQ queue
 *
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 * @example
 * php bin/console app:notification:processing
 */
#[AsCommand(
    name: 'app:notification:processing',
    description: 'Process notifications form database: ',
)]
class NotificationProcessing extends Command
{

    public function __construct(
        private LoggerInterface     $Logger,
        private NotificationService $NotificationService,
        string                      $name = null
    )
    {
        parent::__construct($name);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->Logger->info('app:notification:processing');
        $this->NotificationService->processing();

        return Command::SUCCESS;
    }

}
