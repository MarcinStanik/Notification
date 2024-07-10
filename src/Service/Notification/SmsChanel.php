<?php declare(strict_types=1);

namespace App\Service\Notification;

use App\Service\Notification\Provider\Sms\ISmsProvider;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
class SmsChanel extends AbstractChanel
    implements IChanel
{

    /** @var \class-string[]  */
    protected const array PROVIDER_CLASSES = [
        Provider\Sms\Twilio::class,
    ];

    /**
     * @return bool
     */
    public function send(): bool
    {
        $sendResult = false;
        $NotificationRecipient = $this->getNotificationRecipient();

        /** @var ISmsProvider $SmsProvider */
        foreach ($this->getProviders() as $SmsProvider) {
            $SmsProvider
                ->setMobileTo($NotificationRecipient->getRecipient())
                ->setTextBody($NotificationRecipient->getNotification()->getTextBody());

            $this->UsedProvider = $SmsProvider;

            if ($this->UsedProvider->send() === true) {
                $sendResult = true;
                break;
            }
        }

        return $sendResult;
    }

}
