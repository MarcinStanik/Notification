<?php declare(strict_types=1);

namespace App\Validator\Entity;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Repository\NotificationRecipientRepository;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
class NotificationRecipientConstraintValidator extends ConstraintValidator
{

    /**
     * @param ValidatorInterface $Validator
     * @param NotificationRecipientRepository $NotificationRecipientRepository
     */
    public function __construct(
        private ValidatorInterface              $Validator,
        private NotificationRecipientRepository $NotificationRecipientRepository
    )
    {
    }

    /**
     * @param \App\Entity\NotificationRecipient $NotificationRecipient
     * @param NotificationRecipientConstraint $NotificationRecipientConstraint
     * @return void
     */
    public function validate($NotificationRecipient, Constraint $NotificationRecipientConstraint): void
    {
        if (!$NotificationRecipient instanceof \App\Entity\NotificationRecipient) {
            throw new UnexpectedValueException($NotificationRecipient, \App\Entity\NotificationRecipient::class);
        }

        if (!$NotificationRecipientConstraint instanceof NotificationRecipientConstraint) {
            throw new UnexpectedValueException($NotificationRecipientConstraint, NotificationRecipientConstraint::class);
        }

        if ($NotificationRecipient->getRecipient() === null && $NotificationRecipient->getUserIdentifier() === null) {
            $this->context
                ->buildViolation('One of the Recipient or UserIdentifier fields is required')
                ->atPath('NotificationRecipient.Recipient')
                ->addViolation();
        }
    }

}
