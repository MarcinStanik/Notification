<?php declare(strict_types=1);

namespace App\Service\Notification;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Entity\NotificationRecipient;
use App\Repository\NotificationRecipientRepository;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
abstract class AbstractChanel
{

    /** @var array|null */
    private ?array $Providers = null;

    /** @var IProvider|null - the provider that was used to send notification */
    protected ?IProvider $UsedProvider = null;

    /** @var NotificationRecipient|null */
    private ?NotificationRecipient $NotificationRecipient = null;

    /** @var \class-string[] */
    protected const array PROVIDER_CLASSES = [];

    /**
     * @param ParameterBagInterface $ParameterBag
     * @param NotificationRecipientRepository $NotificationRecipientRepository
     */
    public function __construct(
        private ParameterBagInterface           $ParameterBag,
        private NotificationRecipientRepository $NotificationRecipientRepository
    )
    {
    }

    /**
     * @return NotificationRecipient
     */
    public function getNotificationRecipient(): NotificationRecipient
    {
        return $this->NotificationRecipient;
    }

    /**
     * @param int|NotificationRecipient $notificationRecipient
     * @return $this
     */
    public function setNotificationRecipient(int|NotificationRecipient $notificationRecipient): static
    {
        $this->NotificationRecipient = \is_int($notificationRecipient)
            ? $this->NotificationRecipientRepository->findOneById($notificationRecipient)
            : $notificationRecipient;

        return $this;
    }

    /**
     * @return IProvider|null
     */
    public function getUsedProvider(): ?IProvider
    {
        return $this->UsedProvider;
    }

    /**
     * @return bool
     */
    public function existsActiveProviders(): bool
    {
        return \count($this->getProviders()) > 0;
    }

    /**
     * @return IProvider[]
     */
    protected function getProviders(): array
    {
        if ($this->Providers === null) {
            $this->Providers = [];
            foreach (static::PROVIDER_CLASSES as $providerClassName) {
                /** @var IProvider $Provider */
                $Provider = new $providerClassName($this->ParameterBag);

                if ($Provider->isActive()) {
                    $this->Providers[] = $Provider;
                }
            }
        }

        return $this->Providers;
    }

}
