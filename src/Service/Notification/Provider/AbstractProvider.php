<?php

namespace App\Service\Notification\Provider;

use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;

abstract class AbstractProvider
{

    /** @var string[]|null */
    private ?array $sendReport = null;

    /** @var string[]|null */
    private ?array $errors = null;

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        static $isActive = null;

        if ($isActive === null) {
            $isActive = ($this->ParameterBag->get(static::CONFIG_KEY)['isActive'] ?? null)
                ?? throw new ParameterNotFoundException(static::CONFIG_KEY . '.isActive');
        }

        return $isActive;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        static $name = null;

        if ($name === null) {
            $name = ($this->ParameterBag->get(static::CONFIG_KEY)['name'] ?? null)
                ?? throw new ParameterNotFoundException(static::CONFIG_KEY . '.name');
        }

        return $name;
    }

    /**
     * @param string[] $sendReport
     * @return $this
     */
    public function setSendReport(array $sendReport): static
    {
        $this->sendReport = $sendReport;
        return $this;
    }

    /**
     * @return string[]|null
     */
    public function getSendReport(): array|null
    {
        return $this->sendReport;
    }

    /**
     * @param string[] $errors
     * @return $this
     */
    public function setErrors(array $errors): static
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * @return string[]|null
     */
    public function getErrors(): array|null
    {
        return $this->errors;
    }

    /**
     * @param string $error
     * @return $this
     */
    public function addError(string $error): static
    {
        if ($this->errors === null) {
            $this->errors = [];
        }

        $this->errors[] = $error;

        return $this;
    }

}
