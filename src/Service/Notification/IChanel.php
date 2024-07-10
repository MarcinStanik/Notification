<?php declare(strict_types=1);

namespace App\Service\Notification;

use App\Entity\NotificationRecipient;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
interface IChanel
{

    /**
     * @param int|NotificationRecipient $notificationRecipient
     * @return $this
     */
    public function setNotificationRecipient(int|NotificationRecipient $notificationRecipient): static;

    /**
     * @return bool
     */
    public function send(): bool;

    /**
     * @return IProvider|null
     */
    public function getUsedProvider(): ?IProvider;

}
