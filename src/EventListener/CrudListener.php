<?php

namespace App\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\{
    PrePersistEventArgs,
    PreUpdateEventArgs
};
use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsDoctrineListener(event: Events::prePersist, priority: 100)]
#[AsDoctrineListener(event: Events::preUpdate, priority: 100)]
final class CrudListener
{
    public function __construct(
        #[Autowire('%app.timezone%')]
        private readonly string $timezone,
    ) {}

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        $now = new \DateTimeImmutable('now', new \DateTimeZone($this->timezone));

        // Date de création et modification
        if (
            $this->entityHasMethod($entity, 'setCreatedAt')  &&
            is_null($entity->getCreatedAt())
        ) {
            $entity->setCreatedAt($now);
        }

        if (
            $this->entityHasMethod($entity, 'setUpdatedAt') &&
            is_null($entity->getUpdatedAt())
        ) {
            $entity->setUpdatedAt($now);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        // Date de modification
        if ($this->entityHasMethod($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone($this->timezone)));
        }
    }

    /**
     * Vérifie la présence d'une méthode dans une entité
     * 
     * @param object $entity Entité à vérifier
     * @param string $method Nom de la méthode à vérifier
     */
    private function entityHasMethod(object $entity, string $method): bool
    {
        return in_array($method, get_class_methods($entity));
    }
}
