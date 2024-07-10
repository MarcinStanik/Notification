<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\NotificationRecipientRepository;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Mapping as ORM;
use App\Type\NotificationRecipientStatus;
use App\Component\GlobalFunction;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
#[ORM\Entity(repositoryClass: NotificationRecipientRepository::class)]
#[ORM\Table(name: "notification_recipient")]
#[ORM\HasLifecycleCallbacks]
class NotificationRecipient extends \App\Entity\Base\NotificationRecipient
{

    /**
     * @param string[] $fields
     * @return array
     * @example
     * toArray(['id', 'notification' => ['id', 'chanelType']])
     */
    public function toArray(array $fields = []): array
    {
        $array = [];

        if (\in_array('id', $fields)) {
            $array['id'] = $this->getId();
        }
        if (\in_array('notification', $fields) || \array_key_exists('notification', $fields)) {
            $array['notification'] = $this->getNotification()->toArray(
                \array_key_exists('notification', $fields)
                    ? $fields['notification']
                    : ['id', 'chanelType']
            );
        }
        if (\in_array('recipient', $fields)) {
            $array['recipient'] = $this->getRecipient();
        }
        if (\in_array('status', $fields)) {
            $array['status'] =
                $this->getStatus()
                    ?
                    [
                        'name' => $this->getStatus()->name,
                        'value' => $this->getStatus()->value,
                    ]
                    : null;
        }
        if (\in_array('statusDate', $fields)) {
            $array['statusDate'] = $this->getStatusDate()
                ? GlobalFunction::dateTimeToArray($this->getStatusDate())
                : null;
        }
        if (\in_array('sendAttemptCount', $fields)) {
            $array['sendAttemptCount'] = $this->getSendAttemptCount();
        }
        if (\in_array('sendReport', $fields)) {
            $array['sendReport'] = $this->getSendReport();
        }

        return $array;
    }

    /**
     * @param ClassMetadata $Metadata
     * @return void
     */
    public static function loadValidatorMetadata(ClassMetadata $Metadata): void
    {
        $Metadata
            ->addConstraint(new \App\Validator\Entity\NotificationRecipientConstraint());
    }

    #[ORM\PrePersist]
    public function prePersist(PrePersistEventArgs $eventArgs): void
    {
        if ($this->getStatus() === null) {
            $this->setStatus(NotificationRecipientStatus::NEW);
        }
        if ($this->getSendAttemptCount() === null) {
            $this->setSendAttemptCount(0);
        }
        if ($this->getStatusDate() === null) {
            $this->setStatusDate(new \DateTimeImmutable());
        }
    }

}
