<?php declare(strict_types=1);

namespace App\Service;

use App\Dto\Notification\EmailDto;
use App\Dto\Notification\SmsDto;
use App\Dto\NotificationDto;
use App\Entity\Notification;
use App\Entity\NotificationRecipient;
use App\Repository\NotificationRecipientRepository;
use App\Repository\NotificationRepository;
use App\Service\Notification\EmailChanel;
use App\Service\Notification\IChanel;
use App\Service\Notification\SmsChanel;
use App\Type\NotificationChanelType;
use App\Type\NotificationRecipientStatus;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
class NotificationService
{

    /** @var AMQPStreamConnection|null */
    private ?AMQPStreamConnection $AMQPConnection = null;

    public function __construct(
        private LoggerInterface                 $Logger,
        private ParameterBagInterface           $ParameterBag,
        private RabbitMqService                 $RabbitMqService,
        private NotificationRepository          $NotificationRepository,
        private NotificationRecipientRepository $NotificationRecipientRepository
    )
    {
    }

    /**
     * @param NotificationDto $NotificationDto
     * @return Notification[]
     */
    public function storage(NotificationDto $NotificationDto): array
    {
        $Notifications = [];

        /** @var NotificationChanelType $NotificationChanelType */
        foreach ($NotificationDto->getNotificationChanelTypes() as $NotificationChanelType) {
            $Notification = new Notification();
            $Notification
                ->setChanelType($NotificationChanelType)
                ->setTextBody($NotificationDto->getTextBody());

            if ($NotificationChanelType == NotificationChanelType::EMAIL) {
                $Notification
                    ->setSubject($NotificationDto->getSubject())
                    ->setHtmlBody($NotificationDto->getHtmlBody());
            }

            if ($NotificationDto->getMaxAmountOfNotificationsPerHour() !== null) {
                $Notification->setSettings([
                    'maxAmountOfNotificationsPerHour' => $NotificationDto->getMaxAmountOfNotificationsPerHour(),
                    'chanels' => $NotificationDto->getChanels(),
                ]);
            }

            $this->saveNotification($Notification, $NotificationDto, $NotificationChanelType);

            $Notifications[] = $Notification;
        }

        return $Notifications;
    }

    /**
     * Publish notifications to RabbitMQ queue
     *
     * @return void
     * @throws \Exception
     */
    public function processing(): void
    {
        $maxSendAttemptCount = ($this->ParameterBag->get('notification')['sendAttemptCount'] ?? null)
            ?: throw new ParameterNotFoundException('notification.sendAttemptCount');

        $queueName = ($this->ParameterBag->get('notificationRabbitMq')['queueName'] ?? null)
            ?: throw new ParameterNotFoundException('notificationRabbitMq.queueName');
        $routingKey = ($this->ParameterBag->get('notificationRabbitMq')['routingKey'] ?? null)
            ?: throw new ParameterNotFoundException('notificationRabbitMq.routingKey');

        $NotificationsToSend = $this->NotificationRepository
            ->findToSend($maxSendAttemptCount);

        if (\count($NotificationsToSend) == 0) {
            return;
        }

        /** @var NotificationRecipient[] $NotificationRecipients - where status = NEW or ERROR */
        $NotificationRecipients = $this->NotificationRecipientRepository
            ->findByNotificationsToSend($NotificationsToSend, $maxSendAttemptCount);

        if (\count($NotificationRecipients) == 0) {
            throw new \Exception('If NotificationsToSend exists then NotificationRecipients should also exists');
        }

        /** @var NotificationRecipient $NotificationRecipient */
        foreach ($NotificationRecipients as $NotificationRecipient) {
            $NotificationRecipient
                ->setStatus(NotificationRecipientStatus::IN_PROGRESS)
                ->setStatusDate(new \DateTimeImmutable());

            $result = $this->NotificationRecipientRepository->save($NotificationRecipient, true);

            if ($result instanceof ConstraintViolationListInterface) {
                $this->Logger->critical(\sprint('NotificationRecipient id: %d, validation error: %s',
                    $NotificationRecipient->getId(), $result->get(0)->getMessage()
                ));
            } else {
                $data = [
                    'notificationRecipient' => $NotificationRecipient->toArray(['id'])
                ];

                $this->RabbitMqService->publishMessage($queueName, $routingKey, $data);
            }
        }
    }

    /**
     * Send notifications to recipients
     *
     * @param int|NotificationRecipient $notificationRecipient
     * @return bool
     */
    public function send(int|NotificationRecipient $notificationRecipient): bool
    {
        $resendingDelayTime = ($this->ParameterBag->get('notification')['resendingDelayTime'] ?? null)
            ?: throw new ParameterNotFoundException('notification.resendingDelayTime');

        $NotificationRecipient = is_int($notificationRecipient)
            ? $this->NotificationRecipientRepository->findOneById($notificationRecipient)
            : $notificationRecipient;

        if ($NotificationRecipient === null
            || $NotificationRecipient->getStatus() == NotificationRecipientStatus::SENT
        ) {
            return false;
        }

        /** @var IChanel|null $Changel */
        $Chanel = match ($NotificationRecipient->getNotification()->getChanelType()) {
            NotificationChanelType::EMAIL => new EmailChanel($this->ParameterBag, $this->NotificationRecipientRepository),
            NotificationChanelType::SMS => new SmsChanel($this->ParameterBag, $this->NotificationRecipientRepository),
            default => null,
        };

        try {
            if ($Chanel === null) {
                throw new \Exception(\sprintf('Missing support for chanel: %s', $NotificationRecipient->getNotification()->getChanelType()?->value));
            }

            if ($Chanel->existsActiveProviders() === false) {
                throw new \Exception(\sprintf('Missing active providers for chanel: %s', $NotificationRecipient->getNotification()->getChanelType()?->value));
            }

            // Sending notification by chanel
            $sendResult = $Chanel
                ->setNotificationRecipient($NotificationRecipient)
                ->send();

            if ($sendResult === true) {
                $NotificationRecipient
                    ->setStatus(NotificationRecipientStatus::SENT)
                    ->setSendReport($Chanel->getUsedProvider()?->getSendReport());
            } else {
                $Now = new \DateTime();
                $Now->modify("+$resendingDelayTime minutes");

                $NotificationRecipient
                    ->setStatus(NotificationRecipientStatus::ERROR)
                    ->setSendAttemptCount(($NotificationRecipient->getSendAttemptCount() ?? 0) + 1)
                    ->setSendReport(
                        $Chanel->getUsedProvider()?->getErrors() ?? $Chanel->getUsedProvider()?->getSendReport()
                    )
                    ->setSendAt(\DateTimeImmutable::createFromMutable($Now));

                $this->Logger->critical(\sprintf('Problem to sending NotificationRecipient id: %d', $NotificationRecipient->getId()));
            }

            $NotificationRecipient
                ->setStatusDate(new \DateTimeImmutable())
                ->setProviderName($Chanel->getUsedProvider()?->getName());

            $this->NotificationRecipientRepository->save($NotificationRecipient, true);
        } catch (\Exception $Ee) {
            $this->Logger->critical($Ee->getMessage());

            $NotificationRecipient
                ->setStatus(NotificationRecipientStatus::ERROR)
                ->setStatusDate(new \DateTimeImmutable())
                ->setSendAttemptCount(($NotificationRecipient->getSendAttemptCount() ?? 0) + 1)
                ->setSendReport([$Ee->getMessage()]);

            $this->NotificationRecipientRepository->save($NotificationRecipient, true);
        }

        return $sendResult;
    }

    /**
     * @param Notification $Notification
     * @param NotificationDto $NotificationDto
     * @param NotificationChanelType $NotificationChanelType
     * @return void
     */
    private function saveNotification(Notification $Notification, NotificationDto $NotificationDto, NotificationChanelType $NotificationChanelType): void
    {
        $maxAmountOfNotificationsPerHour = $NotificationDto->getMaxAmountOfNotificationsPerHour();

        $NotificationRecipientCount = 0;
        $Now = null;
        /** @var UserDto $UserDto */
        foreach ($NotificationDto->getUsersDto() as $UserDto) {
            $recipient = match ($NotificationChanelType) {
                NotificationChanelType::EMAIL => $UserDto->getEmail(),
                NotificationChanelType::SMS => $UserDto->getMobile(),
                default => null,
            };

            $NotificationRecipientCount++;

            $NotificationRecipient = new NotificationRecipient();
            $NotificationRecipient
                ->setRecipient($recipient)
                ->setUserIdentifier($UserDto->getId() !== null ? (string)$UserDto->getId() : null)
                ->setSendAt($Now != null ? \DateTimeImmutable::createFromMutable($Now) : null);

            if ($maxAmountOfNotificationsPerHour !== null && $NotificationRecipientCount % $maxAmountOfNotificationsPerHour === 0) {
                if ($Now === null) {
                    $Now = new \DateTime();
                }

                $Now->modify("+1 hours");
            }

            $Notification->addNotificationRecipient($NotificationRecipient);
        }

        $this->NotificationRepository->save($Notification, true);
    }

}
