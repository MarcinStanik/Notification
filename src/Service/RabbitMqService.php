<?php declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
class RabbitMqService
{

    /** @var AMQPStreamConnection|null */
    private ?AMQPStreamConnection $AMQPConnection = null;

    /** @var  array<string, AMQPChannel>  - key = queueName */
    private array $Channels = [];

    /**
     * @param ParameterBagInterface $ParameterBag
     */
    public function __construct(
        private ParameterBagInterface $ParameterBag,
    )
    {
    }

    /**
     * @param string $queueName
     * @param string $routingKey
     * @param array $data
     * @return void
     */
    public function publishMessage(string $queueName, string $routingKey, array $data): void
    {
        $Channel = $this->getChannel($queueName);

        $AMQPMessage = new AMQPMessage(\json_encode($data));
        $Channel->basic_publish($AMQPMessage, '', $routingKey);
    }

    /**
     * @return AMQPStreamConnection
     */
    public function getAMQPConnection(): AMQPStreamConnection
    {
        if ($this->AMQPConnection === null) {
            $url = ($this->ParameterBag->get('rabbitMq')['url'] ?? null) ?: throw new ParameterNotFoundException('rabbitMq.url');
            $port = ($this->ParameterBag->get('rabbitMq')['port'] ?? null) ?: throw new ParameterNotFoundException('rabbitMq.port');
            $user = ($this->ParameterBag->get('rabbitMq')['user'] ?? null) ?: throw new ParameterNotFoundException('rabbitMq.user');
            $password = ($this->ParameterBag->get('rabbitMq')['password'] ?? null) ?: throw new ParameterNotFoundException('rabbitMq.password');

            $this->AMQPConnection = new AMQPStreamConnection($url, $port, $user, $password);
        }

        return $this->AMQPConnection;
    }

    /**
     * @param string $queueName
     * @return AMQPChannel
     */
    public function getChannel(string $queueName): AMQPChannel
    {
        if (!\array_key_exists($queueName, $this->Channels)) {
            $Channel = $this->getAMQPConnection()->channel();
            // $durable = true - means that the queue will survive a restart of the RabbitMQ node.
            // To do this, we need to declare it durable
            $Channel->queue_declare($queueName, false, true, false, false);

            $this->Channels[$queueName] = $Channel;
        }

        return $this->Channels[$queueName];
    }

    public function __destruct()
    {
        foreach ($this->Channels as $Channel) {
            $Channel->close();
        }

        if ($this->AMQPConnection !== null) {
            $this->AMQPConnection->close();
        }
    }

}
