<?php declare(strict_types=1);

namespace App\Validator\Entity;

use Symfony\Component\Validator\Constraint;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
#[\Attribute]
class NotificationRecipientConstraint extends Constraint
{

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }

}
