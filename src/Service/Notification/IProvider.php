<?php declare(strict_types=1);

namespace App\Service\Notification;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
interface IProvider
{

    /**
     * @return bool
     */
    public function send(): bool;

    /**
     * @return bool
     */
    public function isActive(): bool;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string[] $sendReport
     * @return $this
     */
    public function setSendReport(array $sendReport): static;

    /**
     * @return string[]|null
     */
    public function getSendReport(): array|null;

    /**
     * @param string[] $errors
     * @return $this
     */
    public function setErrors(array $errors): static;

    /**
     * @return string[]|null
     */
    public function getErrors(): array|null;

    /**
     * @param string $error
     * @return $this
     */
    public function addError(string $error): static;

}
