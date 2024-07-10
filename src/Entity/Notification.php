<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Component\GlobalFunction;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\Table(name: "notification")]
#[ORM\HasLifecycleCallbacks]
class Notification extends \App\Entity\Base\Notification
{

    use Trait\PrePersistCreatedTrait;

    /**
     * @var Collection<int, NotificationRecipient>
     */
    #[ORM\OneToMany(targetEntity: NotificationRecipient::class, mappedBy: 'Notification', cascade: ["persist", "remove"])]
    private Collection $NotificationRecipients;

    public function __construct()
    {
        $this->NotificationRecipients = new ArrayCollection();
    }

    /**
     * @return Collection<int, NotificationRecipient>
     */
    public function getNotificationRecipients(): Collection
    {
        return $this->NotificationRecipients;
    }

    public function addNotificationRecipient(NotificationRecipient $notificationRecipient): static
    {
        if (!$this->NotificationRecipients->contains($notificationRecipient)) {
            $this->NotificationRecipients->add($notificationRecipient);
            $notificationRecipient->setNotification($this);
        }

        return $this;
    }

    public function removeNotificationRecipient(NotificationRecipient $notificationRecipient): static
    {
        if ($this->NotificationRecipients->removeElement($notificationRecipient)) {
            // set the owning side to null (unless already changed)
            if ($notificationRecipient->getNotification() === $this) {
                $notificationRecipient->setNotification(null);
            }
        }

        return $this;
    }

    /**
     * @param string[] $fields
     * @return array
     */
    public function toArray(array $fields = []): array
    {
        $array = [];

        if (\in_array('id', $fields)) {
            $array['id'] = $this->getId();
        }
        if (\in_array('chanelType', $fields)) {
            $array['chanelType'] = [
                'name' => $this->getChanelType()->name,
                'value' => $this->getChanelType()->value,
            ];
        }
        if (\in_array('subject', $fields)) {
            $array['subject'] = $this->getSubject();
        }
        if (\in_array('textBody', $fields)) {
            $array['textBody'] = $this->getTextBody();
        }
        if (\in_array('htmlBody', $fields)) {
            $array['htmlBody'] = $this->getHtmlBody();
        }
        if (\in_array('createdAt', $fields)) {
            $array['createdAt'] = GlobalFunction::dateTimeToArray($this->getCreatedAt());
        }

        return $array;
    }

}
