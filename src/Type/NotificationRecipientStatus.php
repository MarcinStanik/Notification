<?php declare(strict_types=1);

namespace App\Type;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
enum NotificationRecipientStatus: string
{
    case NEW = 'NEW';
    case IN_PROGRESS = 'IN_PROGRESS';
    case SENT = 'SENT';
    case ERROR = 'ERROR';
}
