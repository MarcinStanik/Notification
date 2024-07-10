<?php declare(strict_types=1);

namespace App\Service\Notification\Provider\Email;

use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Service\Notification\Provider\AbstractProvider;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
abstract class AbstractEmailProvider extends AbstractProvider
{

    /** @var string|null */
    private ?string $emailTo = null;

    /** @var string|null */
    private ?string $subject = null;

    /** @var string|null */
    private ?string $textBody = null;

    /** @var string|null */
    private ?string $htmlBody = null;

    /**
     * @param ParameterBagInterface $ParameterBag
     */
    public function __construct(
        protected ParameterBagInterface $ParameterBag
    )
    {
    }

    /**
     * @return string|null
     */
    public function getEmailTo(): ?string
    {
        return $this->emailTo;
    }

    public function setEmailTo(?string $emailTo): static
    {
        $this->emailTo = $emailTo;
        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): static
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

    /**
     * @return string
     */
    protected function getSenderEmail(): string
    {
        static $senderEmail = null;

        if ($senderEmail === null) {
            $senderEmail = ($this->ParameterBag->get(static::CONFIG_KEY)['senderEmail'] ?? null)
                ?: throw new ParameterNotFoundException(static::CONFIG_KEY . '.senderEmail');
        }

        return $senderEmail;
    }

}
