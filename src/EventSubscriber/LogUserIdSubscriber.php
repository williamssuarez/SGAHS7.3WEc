<?php

namespace App\EventSubscriber;

use App\Entity\LogEntry;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Bundle\SecurityBundle\Security;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: LogEntry::class)]
readonly class LogUserIdSubscriber
{
    public function __construct(
        private Security $security
    ) {}

    public function prePersist(LogEntry $logEntry, LifecycleEventArgs $event): void
    {
        $user = $this->security->getUser();

        if ($user && method_exists($user, 'getId')) {
            $logEntry->setUserId($user->getId());
        }
    }
}
