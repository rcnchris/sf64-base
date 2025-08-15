<?php

namespace App\EventListener;

use App\Entity\Log;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\{
    PostPersistEventArgs,
    PostRemoveEventArgs,
    PostUpdateEventArgs,
    PrePersistEventArgs,
    PreRemoveEventArgs,
    PreUpdateEventArgs
};
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsDoctrineListener(event: Events::prePersist, priority: 100)]
#[AsDoctrineListener(event: Events::postPersist, priority: 100)]
#[AsDoctrineListener(event: Events::preUpdate, priority: 100)]
#[AsDoctrineListener(event: Events::postUpdate, priority: 100)]
#[AsDoctrineListener(event: Events::preRemove, priority: 100)]
#[AsDoctrineListener(event: Events::postRemove, priority: 100)]
final class CrudListener
{
    private array $removesEntities = [];

    public function __construct(
        #[Autowire('%app.timezone%')]
        private readonly string $timezone,
        private readonly LoggerInterface $dbLogger,
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

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Log) {
            return;
        }
        $entityName = $this->getEntityShortName($entity);
        $this->dbLogger->info(sprintf('Ajout %s', $entityName), [
            'action' => 'add',
            'entity' => $entityName,
            'entity_id' => $entity->getId(),
        ]);
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        // Date de modification
        if ($this->entityHasMethod($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone($this->timezone)));
        }
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Log) {
            return;
        }

        /** @var EntityManager $em */
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();
        $uow->computeChangeSet($em->getClassMetadata(get_class($entity)), $entity);
        $changes = $uow->getEntityChangeSet($entity);
        if (array_key_exists('updatedAt', $changes)) {
            unset($changes['updatedAt']);
        }
        if (!empty($changes)) {
            $entityName = $this->getEntityShortName($entity);
            $this->dbLogger->info(sprintf('Modification %s', $entityName), [
                'action' => 'update',
                'entity' => $entityName,
                'entity_id' => $entity->getId(),
                'changes' => $changes
            ]);
        }
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Log) {
            return;
        }
        $this->removesEntities[] = [
            'entity' => $this->getEntityShortName($entity),
            'entity_id' => $entity->getId(),
        ];
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Log) {
            return;
        }

        foreach ($this->removesEntities as $removed) {
            $this->dbLogger->info(sprintf('Suppression %s', $removed['entity']), [
                'action' => 'remove',
                'entity' => $removed['entity'],
                'entity_id' => $removed['entity_id'],
            ]);
        }
        $this->removesEntities = [];
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

    /**
     * Retourne le nom d'une entité
     * 
     * @param object $entité Instance d'une entité
     */
    private function getEntityShortName(object $entity): string 
    {
        $className = get_class($entity);
        $classNameParts = explode('\\', $className);
        return array_pop($classNameParts);
    }
}
