<?php

// src/Doctrine/SoftDeletableSubscriber.php

namespace App\Doctrine;

use App\Entity\Traits\SoftDeletetableTrait;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security; // For Symfony 6.2+
use App\Entity\User; // Your User entity class

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
final class SoftDeletetableSubscriber
{
    // Inject the Security service to get the current user
    public function __construct(private readonly Security $security){}

    public function prePersist(PrePersistEventArgs $args): void
{
    $entity = $args->getObject();

    // 1. Only act on entities that use the SoftDeletableTrait
    if (!in_array(SoftDeletetableTrait::class, class_uses($entity))) {
        return;
    }

    $now = new \DateTimeImmutable();
    $user = $this->security->getUser();

    /** @var SoftDeletableTrait|object $entity */

    // 2. Set 'created' and 'updated' timestamps
    $entity->setCreated($now);
    $entity->setUpdated($now); // Initial creation updates both

    // 3. Set 'uidCreate' and 'uidUpdate'
    if ($user instanceof User) {
        $entity->setUidCreate($user);
        $entity->setUidUpdate($user);
    }
}

    public function preUpdate(PreUpdateEventArgs $args): void
{
    $entity = $args->getObject();

    if (!in_array(SoftDeletableTrait::class, class_uses($entity))) {
        return;
    }

    $now = new \DateTimeImmutable();
    $user = $this->security->getUser();

    /** @var SoftDeletableTrait|object $entity */

    // 4. Set only the 'updated' timestamp
    $entity->setUpdated($now);

    // 5. Set only 'uidUpdate'
    if ($user instanceof User) {
        $entity->setUidUpdate($user);
    }

    // 6. Force Doctrine to recognize the change on a lifecycle event
    $args->getObjectManager()->getUnitOfWork()->recomputeSingleEntityChangeSet(
        $args->getObjectManager()->getClassMetadata($entity::class),
        $entity
    );
}
}
