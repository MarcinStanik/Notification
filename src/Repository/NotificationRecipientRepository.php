<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\NotificationRecipient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Type\NotificationRecipientStatus;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @extends ServiceEntityRepository<NotificationRecipient>
 *
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
class NotificationRecipientRepository extends ServiceEntityRepository
{

    /**
     * @param ManagerRegistry $registry
     * @param ValidatorInterface $Validator
     */
    public function __construct(
        ManagerRegistry $registry,
        private ValidatorInterface $Validator,
    )
    {
        parent::__construct($registry, NotificationRecipient::class);
    }

    /**
     * @param int $id
     * @return NotificationRecipient|null
     */
    public function findOneById(int $id): ?NotificationRecipient
    {
        return $this->createQueryBuilder('NotificationRecipient')
            ->select(['NotificationRecipient', 'Notification'])
            ->join('NotificationRecipient.Notification', 'Notification')
            ->where('NotificationRecipient.id = :id')->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int|Notification $notification
     * @param int $maxSendAttemptCount
     * @return array
     */
    public function findByNotificationToSend(int|Notification $notification, int $maxSendAttemptCount = 100): array
    {
        return $this->findByNotificationsToSend([$notification], $maxSendAttemptCount);
    }

    /**
     * all with status NEW or ERROR
     *
     * @param int[]|Notification[] $notifications
     * @param int $maxSendAttemptCount
     * @return NotificationRecipient[]
     */
    public function findByNotificationsToSend(array $notifications, int $maxSendAttemptCount = 100): array
    {
        $notificationIds = [];

        foreach ($notifications as $notification) {
            $notificationIds[] = ($notification instanceof Notification)
                ? $notification->getId()
                : $notification;
        }

        if (\count($notificationIds) == 0) {
            return [];
        }

        return $this->createQueryBuilder('NotificationRecipient')
            ->select('NotificationRecipient')
            ->where('NotificationRecipient.Notification IN (:notificationIds)')->setParameter('notificationIds', $notificationIds)
            ->andWhere('NotificationRecipient.Status = :newStatus OR (NotificationRecipient.Status = :errorStatus AND NotificationRecipient.sendAttemptCount < :maxSendAttemptCount)')
            ->setParameter('newStatus', NotificationRecipientStatus::NEW)
            ->setParameter('errorStatus', NotificationRecipientStatus::ERROR)
            ->setParameter('maxSendAttemptCount', $maxSendAttemptCount)
            ->andWhere('NotificationRecipient.SendAt IS NULL OR NotificationRecipient.SendAt <= :sendAt')->setParameter('sendAt', new \DateTimeImmutable())
            ->orderBy('NotificationRecipient.StatusDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param NotificationRecipient $NotificationRecipient
     * @param bool $flush
     * @param bool $validate
     * @return true|ConstraintViolationListInterface
     */
    public function save(NotificationRecipient $NotificationRecipient, bool $flush = false, bool $validate = true): true|ConstraintViolationListInterface
    {
        if ($validate === true
            && ($Errors = $this->Validator->validate($NotificationRecipient))->count() > 0
        ) {
            return $Errors;
        }

        $this->getEntityManager()->persist($NotificationRecipient);

        if ($flush) {
            $this->getEntityManager()->flush();
        }

        return true;
    }

    /**
     * @param NotificationRecipient $NotificationRecipient
     * @param bool $flush
     * @return void
     */
    public function remove(NotificationRecipient $NotificationRecipient, bool $flush = false): void
    {
        $this->getEntityManager()->remove($NotificationRecipient);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
