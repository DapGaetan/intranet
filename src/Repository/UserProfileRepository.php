<?php

namespace App\Repository;

use App\Entity\UserProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs as EventLifecycleEventArgs;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class UserProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserProfile::class);
    }

    public function preUpdate(EventLifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof UserProfile) {
            return;
        }

        $entity->setUpdatedAt(new \DateTimeImmutable());
    }
}
