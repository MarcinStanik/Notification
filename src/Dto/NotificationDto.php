<?php declare(strict_types=1);

namespace App\Dto;

use App\Type\NotificationChanelType;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
class NotificationDto
    implements IDto
{

    /** @var NotificationChanelType[]|null */
    private ?array $NotificationChanelTypes = null;

    /** @var UserDto[]|null  */
    private ?array $UsersDto = null;

    /**
     * @param array|null $recipients
     * @param string|null $subject
     * @param string|null $textBody
     * @param string|null $htmlBody
     * @param int|null $maxAmountOfNotificationsPerHour
     * @param string[]|null $chanels
     */
    public function __construct(
        private ?array  $recipients = null,
        private ?string $subject = null,
        private ?string $textBody = null,
        private ?string $htmlBody = null,
        private ?int    $maxAmountOfNotificationsPerHour = null,
        private ?array  $chanels = null
    )
    {
        if ($chanels !== null) {
            $this->NotificationChanelTypes = [];
            foreach ($chanels as $change) {
                $this->NotificationChanelTypes[] = NotificationChanelType::from($change);
            }
        }

        if ($recipients !== null) {
            $this->UsersDto = [];
            foreach ($recipients as $recipient) {
                $this->UsersDto[] = UserDto::factory($recipient);
            }
        }
    }

    /**
     * @param array $data
     * @return static
     */
    public static function factory(array $data): static
    {
        return new self(
            $data['recipients'] ?? null,
            $data['subject'] ?? null,
            $data['text_body'] ?? null,
            $data['html_body'] ?? null,
            $data['maxAmountOfNotificationsPerHour'] ?? null,
            $data['chanels'] ?? null
        );;
    }

    /**
     * @return string[]
     */
    public function getRecipients(): array
    {
        return $this->recipients ?? [];
    }

    /**
     * @return string|null
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * @return string|null
     */
    public function getTextBody(): ?string
    {
        if ($this->textBody === null && $this->htmlBody !== null) {
            $this->textBody = $this->htmlBody;

            $this->textBody = \strip_tags($this->textBody, '<br><p>');
            $this->textBody = \str_replace(['<p>', '<br>', '</p>'], ["\n\n", "\n", ''], $this->textBody);
        }

        return $this->textBody;
    }

    /**
     * @return string|null
     */
    public function getHtmlBody(): ?string
    {
        if ($this->htmlBody === null && $this->textBody !== null) {
            $this->htmlBody = $this->textBody;
            $this->htmlBody = \str_replace("\n", '<br>', $this->htmlBody);
        }

        return $this->htmlBody;
    }

    /**
     * @return int|null
     */
    public function getMaxAmountOfNotificationsPerHour(): ?int
    {
        return $this->maxAmountOfNotificationsPerHour;
    }

    /**
     * @return string[]|null
     */
    public function getChanels(): ?array
    {
        return $this->chanels;
    }

    /**
     * @return UserDto[]|null
     */
    public function getUsersDto(): ?array
    {
        return $this->UsersDto;
    }

    /**
     * @return NotificationChanelType[]|null
     */
    public function getNotificationChanelTypes(): ?array
    {
        return $this->NotificationChanelTypes;
    }

    /**
     * @return string[]
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->getNotificationChanelTypes() === null || \count($this->getNotificationChanelTypes()) == 0) {
            $errors[] = 'At least one chanel is required';
        }

        if (\count($this->getRecipients()) == 0) {
            $errors[] = 'At least one recipient is required';
        }

        if ($this->getTextBody() == '') {
            $errors[] = 'Text body is a required field.';
        }

        if ($this->getNotificationChanelTypes() !== null) {
            if (\in_array(NotificationChanelType::EMAIL, $this->getNotificationChanelTypes())) {
                if ((string)$this->getSubject() == '') {
                    $errors[] = 'Subject is a required field.';
                }

                if ((string)$this->getHtmlBody() == '') {
                    $errors[] = 'Html body is a required field.';
                }

                /** @var UserDto $UserDto */
                foreach ($this->getUsersDto() as $UserDto) {
                    if ((string)$UserDto->getEmail() == '') {
                        $errors[] = \sprintf('User "%s" - missing email.', $UserDto->getName());
                        continue;
                    }
                    if (!\filter_var($UserDto->getEmail(), \FILTER_VALIDATE_EMAIL)) {
                        $errors[] = \sprintf('User "%s", "%s "is not a valid email address.', $UserDto->getName(), $UserDto->getEmail());
                    }
                }
            }

            if (\in_array(NotificationChanelType::SMS, $this->getNotificationChanelTypes())) {
                /** @var UserDto $UserDto */
                foreach ($this->getUsersDto() as $UserDto) {
                    if ((string)$UserDto->getMobile() == '') {
                        $errors[] = \sprintf('User "%s" - missing mobile.', $UserDto->getName());
                        continue;
                    }
                    if (!\preg_match('/^\+?[1-9]\d{1,14}$/', $UserDto->getMobile())) {
                        $errors[] = \sprintf('User "%s", "%s" is not a valid mobile number.', $UserDto->getName(), $UserDto->getMobile());
                    }
                }
            }
        }

        return $errors;
    }

}
