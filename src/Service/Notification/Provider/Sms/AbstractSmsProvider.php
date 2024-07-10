<?php declare(strict_types=1);

namespace App\Service\Notification\Provider\Sms;

use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Service\Notification\Provider\AbstractProvider;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
abstract class AbstractSmsProvider extends AbstractProvider
{

    /** @var string|null */
    private ?string $mobileTo = null;

    /** @var string|null */
    private ?string $textBody = null;

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
    public function getMobileTo(): ?string
    {
        return $this->mobileTo;
    }

    public function setMobileTo(?string $mobileTo): static
    {
        $this->mobileTo = $mobileTo;
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

    /**
     * @return string
     */
    protected function getSenderMobile(): string
    {
        static $senderMobile = null;

        if ($senderMobile === null) {
            $senderMobile = ($this->ParameterBag->get(static::CONFIG_KEY)['senderMobile'] ?? null)
                ?: throw new ParameterNotFoundException(static::CONFIG_KEY . '.senderMobile');
        }

        return $senderMobile;
    }

}
