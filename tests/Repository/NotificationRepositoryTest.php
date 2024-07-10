<?php declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Notification;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
class NotificationRepositoryTest extends AbstractKernelTestCase
{

    public function testFindToSend()
    {
        $Notifications = $this->EntityManager
            ->getRepository(Notification::class)
            ->findToSend();

        $this->assertIsArray($Notifications);
        $this->assertGreaterThanOrEqual(0, \count($Notifications));

        if (\count($Notifications) > 0) {
            $Notification = reset($Notifications);
            $this->assertInstanceOf(Notification::class, $Notification);
        }
    }

}
