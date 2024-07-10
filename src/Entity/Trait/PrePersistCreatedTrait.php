<?php declare(strict_types=1);

namespace App\Entity\Trait;

use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
trait PrePersistCreatedTrait
{

    #[ORM\PrePersist]
    public function prePersistCreated(PrePersistEventArgs $eventArgs): void
    {
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new \DateTimeImmutable());
        }
    }

}
