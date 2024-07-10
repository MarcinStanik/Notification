<?php declare(strict_types=1);

namespace App\Type;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
enum NotificationChanelType: string
{

    case EMAIL = 'EMAIL';
    case SMS = 'SMS';
    case PUSH_NOTIFICATION = 'PUSH_NOTIFICATION'; // TODO
    case FACEBOOK_MESSENGER = 'FACEBOOK_MESSENGER'; // TODO

}
