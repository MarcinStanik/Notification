<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Type\NotificationRecipientStatus;

/**
 * @extends ServiceEntityRepository<Notification>
 *
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
class NotificationRepository extends ServiceEntityRepository
{

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * @param int $ids
     * @return Notification[]
     */
    public function findByIds(array $ids): array
    {
        return $this->findBy([
            'id' => $ids,
        ]);
    }

    /**
     * @param int $maxSendAttemptCount
     * @return array|Notification[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function findToSend(int $maxSendAttemptCount = 100): array
    {
        $params = [];

        $sql = '
SELECT Notification.id
FROM notification AS  Notification
WHERE EXISTS (
                SELECT 1
                FROM notification_recipient AS NotificationRecipient
                WHERE
                    NotificationRecipient.notification_id = Notification.id
                    AND (
                        NotificationRecipient.status = :newStatus
                        OR
                        NotificationRecipient.status = :errorStatus AND NotificationRecipient.send_attempt_count < :maxSendAttemptCount
                    )
                    AND (
                        NotificationRecipient.send_at IS NULL OR NotificationRecipient.send_at <= :sendAt
                    )
                LIMIT 1
            )
        ';

        $params['newStatus'] = NotificationRecipientStatus::NEW->value;
        $params['errorStatus'] = NotificationRecipientStatus::ERROR->value;
        $params['maxSendAttemptCount'] = $maxSendAttemptCount;
        $params['sendAt'] = (new \DateTime())->format('Y-m-d H:i:s');

        $Connection = $this->getEntityManager()->getConnection();
        $Stmt = $Connection->prepare($sql);

        $notificationIds =  \array_map(
            fn(array $notification): int => $notification['id'],
            $Stmt->executeQuery($params)->fetchAllAssociative()
        );

        return \count($notificationIds) > 0
            ? $this->findByIds($notificationIds) :
            [];
    }

    /**
     * @param Notification $Notification
     * @param bool $flush
     * @return void
     */
    public function save(Notification $Notification, bool $flush = false): void
    {
        $this->getEntityManager()->persist($Notification);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param Notification $Notification
     * @param bool $flush
     * @return void
     */
    public function remove(Notification $Notification, bool $flush = false): void
    {
        $this->getEntityManager()->remove($Notification);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
