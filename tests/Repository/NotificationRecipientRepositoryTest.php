<?php declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Notification;
use App\Entity\NotificationRecipient;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
class NotificationRecipientRepositoryTest extends AbstractKernelTestCase
{

    public function testFindOnById()
    {
        $NotificationRecipient = $NotificationRecipients = $this->EntityManager
            ->getRepository(NotificationRecipient::class)
            ->findOneById(1);

        if ($NotificationRecipient !==  null) {
            $this->assertInstanceOf(NotificationRecipient::class, $NotificationRecipient);
        }
    }

    public function testFindByNotificationToSend()
    {
        $Notifications = $this->EntityManager
            ->getRepository(Notification::class)
            ->findToSend();

        $this->assertIsArray($Notifications);

        if (\count($Notifications) > 0) {
            foreach ($Notifications as $Notification) {
                $NotificationRecipients = $this->EntityManager
                    ->getRepository(NotificationRecipient::class)
                    ->findByNotificationToSend($Notification);

                $this->assertIsArray($NotificationRecipients);
                $this->assertGreaterThanOrEqual(1, \count($NotificationRecipients));

                if (\count($NotificationRecipients) > 0) {
                    $NotificationRecipient = reset($NotificationRecipients);
                    $this->assertInstanceOf(NotificationRecipient::class, $NotificationRecipient);
                }
            }
        }
    }

}
