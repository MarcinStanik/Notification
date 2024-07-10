<?php declare(strict_types=1);

namespace App\Entity\Base;

use App\Entity\Notification;
use App\Type\NotificationRecipientStatus;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
#[ORM\MappedSuperclass()]
class NotificationRecipient
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ["unsigned" => true])]
    protected ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'NotificationRecipients')]
    #[ORM\JoinColumn(nullable: false)]
    protected ?Notification $Notification = null;

    #[ORM\Column(length: 256)]
    protected ?string $recipient = null;

    #[ORM\Column(length: 128, nullable: true)]
    protected ?string $userIdentifier = null;

    #[ORM\Column(enumType: NotificationRecipientStatus::class)]
    protected ?NotificationRecipientStatus $Status = null;

    #[ORM\Column(nullable: true)]
    protected ?\DateTimeImmutable $StatusDate = null;

    #[ORM\Column(length: 64, nullable: true)]
    protected ?string $providerName = null;

    #[ORM\Column(nullable: true)]
    protected ?\DateTimeImmutable $SendAt = null;

    #[ORM\Column(type: Types::SMALLINT)]
    protected ?int $sendAttemptCount = null;

    #[ORM\Column(nullable: true)]
    protected ?array $sendReport = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNotification(): ?Notification
    {
        return $this->Notification;
    }

    public function setNotification(?Notification $Notification): static
    {
        $this->Notification = $Notification;

        return $this;
    }

    public function getRecipient(): ?string
    {
        return $this->recipient;
    }

    public function setRecipient(string $recipient): static
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function getUserIdentifier(): ?string
    {
        return $this->userIdentifier;
    }

    public function setUserIdentifier(?string $userIdentifier): static
    {
        $this->userIdentifier = $userIdentifier;
        return $this;
    }

    public function getStatus(): ?NotificationRecipientStatus
    {
        return $this->Status;
    }

    public function setStatus(NotificationRecipientStatus $Status): static
    {
        $this->Status = $Status;

        return $this;
    }

    public function getStatusDate(): ?\DateTimeImmutable
    {
        return $this->StatusDate;
    }

    public function setStatusDate(\DateTimeImmutable $StatusDate): static
    {
        $this->StatusDate = $StatusDate;
        return $this;
    }

    public function getProviderName(): ?string
    {
        return $this->providerName;
    }

    public function setProviderName(?string $providerName): static
    {
        $this->providerName = $providerName;
        return $this;
    }

    public function getSendAt(): ?\DateTimeImmutable
    {
        return $this->SendAt;
    }

    public function setSendAt(?\DateTimeImmutable $SendAt): static
    {
        $this->SendAt = $SendAt;
        return $this;
    }

    public function getSendAttemptCount(): ?int
    {
        return $this->sendAttemptCount;
    }

    public function setSendAttemptCount(int $sendAttemptCount): static
    {
        $this->sendAttemptCount = $sendAttemptCount;

        return $this;
    }

    public function getSendReport(): ?array
    {
        return $this->sendReport;
    }

    public function setSendReport(?array $sendReport): static
    {
        $this->sendReport = $sendReport;

        return $this;
    }

}
