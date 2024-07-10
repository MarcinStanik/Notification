<?php declare(strict_types=1);

namespace App\Service\Notification;

use App\Service\Notification\Provider\Email\IEmailProvider;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
class EmailChanel extends AbstractChanel
    implements IChanel
{

    /** @var \class-string[]  */
    protected const array PROVIDER_CLASSES = [
        Provider\Email\AmazonSES::class,
        Provider\Email\MarcinSMTP::class,
    ];

    /**
     * @return bool
     */
    public function send(): bool
    {
        $sendResult = false;
        $NotificationRecipient = $this->getNotificationRecipient();

        /** @var IEmailProvider $EmailProvider */
        foreach ($this->getProviders() as $EmailProvider) {
            $EmailProvider
                ->setEmailTo($NotificationRecipient->getRecipient())
                ->setSubject($NotificationRecipient->getNotification()->getSubject())
                ->setTextBody($NotificationRecipient->getNotification()->getTextBody())
                ->setHtmlBody($NotificationRecipient->getNotification()->getHtmlBody());

            $this->UsedProvider = $EmailProvider;

            if ($this->UsedProvider->send() === true) {
                $sendResult = true;
                break;
            }
        }

        return $sendResult;
    }

}
