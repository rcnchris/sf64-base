<?php

namespace App\Repository;

use App\Entity\User;
use App\Repository\Trait\AppRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\{PasswordAuthenticatedUserInterface, PasswordUpgraderInterface};

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    use AppRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if ($user instanceof User) {
            $user->setPassword($newHashedPassword);
            $this->save($user);
        }
    }

    /**
     * Crée le QueryBuilder de recherche d'un utilitaeur par son pseudo ou son email
     * 
     * @param string $identifier Pseudo ou adresse mail
     */
    public function getByPseudoOrEmailQb(string $identifier): QueryBuilder
    {
        return $this
            ->createQueryBuilder('u')
            ->where('u.email = :identifier OR u.pseudo = :identifier')
            ->setParameter('identifier', $identifier)
            ->setMaxResults(1);
    }

    /**
     * Trouve un utilisateur par son pseudo ou son email
     * 
     * @param string $identifier Pseudo ou adresse mail
     */
    public function getByPseudoOrEmail(string $identifier): ?User
    {
        return $this
            ->getByPseudoOrEmailQb($identifier)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve un utilisateur par son pseudo ou son email pour authentification
     * 
     * @param string $identifier Pseudo ou adresse mail
     */
    public function getForAuthentication(string $identifier): ?User
    {
        return $this
            ->getByPseudoOrEmailQb($identifier)
            ->andWhere('u.isVerified = 1 AND JSON_SEARCH(u.roles, \'ROLE_CLOSE\') is null')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
