<?php declare(strict_types=1);

namespace App\Service\Notification\Provider\Email;

use App\Service\Notification\IProvider;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
interface IEmailProvider extends IProvider
{

    /**
     * @return string|null
     */
    public function getEmailTo(): ?string;

    /**
     * @param string $emailTo
     * @return $this
     */
    public function setEmailTo(string $emailTo): static;

    /**
     * @return string|null
     */
    public function getSubject(): ?string;

    /**
     * @param string $subject
     * @return $this
     */
    public function setSubject(string $subject): static;

    /**
     * @return string|null
     */
    public function getTextBody(): ?string;

    /**
     * @param string $textBody
     * @return $this
     */
    public function setTextBody(string $textBody): static;

    /**
     * @return string|null
     */
    public function getHtmlBody(): ?string;

    /**
     * @param string $htmlBody
     * @return $this
     */
    public function setHtmlBody(string $htmlBody): static;

}
