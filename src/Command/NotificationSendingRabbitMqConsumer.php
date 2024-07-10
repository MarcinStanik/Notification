<?php declare(strict_types=1);

namespace App\Command;

use App\Service\RabbitMqService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;
use App\Service\NotificationService;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 * @example
 * php bin/console app:notification:sending:rabbit-mq-consumer
 */
#[AsCommand(
    name: 'app:notification:sending:rabbit-mq-consumer',
    description: 'Process notifications form database: ',
)]
class NotificationSendingRabbitMqConsumer extends Command
{

    public function __construct(
        private ParameterBagInterface $ParameterBag,
        private LoggerInterface       $Logger,
        private NotificationService   $NotificationService,
        private RabbitMqService       $RabbitMqService,
        string                        $name = null
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
        return Command::FAILURE;

//        try {
//            $queueName = ($this->ParameterBag->get('notificationRabbitMq')['queueName'] ?? null) ?: throw new ParameterNotFoundException('notificationRabbitMq.queueName');
//
//            $connection = new AMQPStreamConnection('rabbitmq', 5672, 'user', 'password'
//                , connection_timeout:5, read_write_timeout: 5
//            );
//            $channel = $connection->channel();
//
//            $channel->queue_declare($queueName, false, true, false, false);
//
//            echo " [*] Waiting for messages. To exit press CTRL+C\n";
//
//            $callback = function ($msg) {
//                echo ' [x] Received ', $msg->body, "\n";
//            };
//
//            $channel->basic_consume($queueName, '', false, true, false, false, $callback);
//
////        while($channel->is_consuming()) {
////            $channel->wait();
////        }
//
//            while (count($channel->callbacks)) {
//                echo 'wait', "\n";
//                $channel->wait();
//            }
//
//
//            $channel->close();
//            $connection->close();
//        } catch (\Exception $e) {
//            dump($e);
//            echo 'Error: ', $e->getMessage(), "\n";
//        }

        return Command::SUCCESS;
    }

}
