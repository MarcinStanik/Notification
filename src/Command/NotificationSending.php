<?php declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use App\Service\NotificationService;

/**
 *
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 * @example
 * php bin/console app:notification:sending
 */
#[AsCommand(
    name: 'app:notification:sending',
    description: 'Process notifications form database: ',
)]
class NotificationSending extends Command
{

    /**
     * @param LoggerInterface $Logger
     * @param NotificationService $NotificationService
     * @param string|null $name
     */
    public function __construct(
        private LoggerInterface     $Logger,
        private NotificationService $NotificationService,
        string                      $name = null
    )
    {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Send notification by NotificationRecipient data')
            ->addArgument('notificationRecipient', InputArgument::REQUIRED, 'notificationRecipient - json as a string');
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        echo 'app:notification:sending - START' . PHP_EOL;
        $notificationRecipient = $input->getArgument('notificationRecipient');

        if (!\json_validate($notificationRecipient)) {
            $this->Logger->error(\sprintf('notificationRecipient is not a JSON: %s', $notificationRecipient));

            echo 'app:notification:sending - notificationRecipient is not a JSON' . PHP_EOL;
            return Command::FAILURE;
        }

        $notificationRecipient = json_decode($notificationRecipient, true);

        if (!isset($notificationRecipient['notificationRecipient']['id'])) {
            $this->Logger->error('notificationRecipient missing information about id');

            echo 'app:notification:sending - notificationRecipient missing information about id' . PHP_EOL;
            return Command::FAILURE;
        }

        echo 'app:notification:sending - START sending notification' . PHP_EOL;
        $this->Logger->info('app:notification:sending');

        $notificationRecipientId = (int)$notificationRecipient['notificationRecipient']['id'];
        $this->NotificationService->send($notificationRecipientId);

        return Command::SUCCESS;
    }

}
