<?php declare(strict_types=1);

namespace App\Service\Notification\Provider\Sms;

use App\Service\Notification\IProvider;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
interface ISmsProvider extends IProvider
{

    /**
     * @return string|null
     */
    public function getMobileTo(): ?string;

    /**
     * @param string $mobileTo
     * @return $this
     */
    public function setMobileTo(string $mobileTo): static;

    /**
     * @return string|null
     */
    public function getTextBody(): ?string;

    /**
     * @param string $textBody
     * @return $this
     */
    public function setTextBody(string $textBody): static;

}
