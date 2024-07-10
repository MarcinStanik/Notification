<?php declare(strict_types=1);

namespace App\Entity\Base;

use App\Type\NotificationChanelType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
#[ORM\MappedSuperclass()]
class Notification
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ["unsigned" => true])]
    protected ?int $id = null;

    #[ORM\Column(enumType: NotificationChanelType::class)]
    protected ?NotificationChanelType $chanelType = null;

    #[ORM\Column(length: 1024, nullable: true)]
    protected ?string $subject = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $textBody = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $htmlBody = null;

    #[ORM\Column(nullable: true)]
    protected ?array $settings = null;

    #[ORM\Column]
    protected ?\DateTimeImmutable $CreatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChanelType(): ?NotificationChanelType
    {
        return $this->chanelType;
    }

    public function setChanelType(NotificationChanelType $chanelType): static
    {
        $this->chanelType = $chanelType;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getTextBody(): ?string
    {
        return $this->textBody;
    }

    public function setTextBody(?string $textBody): static
    {
        $this->textBody = $textBody;

        return $this;
    }

    public function getHtmlBody(): ?string
    {
        return $this->htmlBody;
    }

    public function setHtmlBody(?string $htmlBody): static
    {
        $this->htmlBody = $htmlBody;

        return $this;
    }

    public function getSettings(): ?array
    {
        return $this->settings;
    }

    public function setSettings(?array $settings): static
    {
        $this->settings = $settings;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(\DateTimeImmutable $CreatedAt): static
    {
        $this->CreatedAt = $CreatedAt;

        return $this;
    }

}
