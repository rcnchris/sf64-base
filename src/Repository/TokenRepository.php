<?php

namespace App\Repository;

use App\Entity\{Token, User};
use App\Repository\Trait\AppRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Token>
 */
class TokenRepository extends ServiceEntityRepository
{
    use AppRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Token::class);
    }

    /**
     * Retourne l'utilisateur d'un jeton
     */
    public function getUserByToken(string $token): ?User
    {
        $token = $this->findOneBy(compact('token'));
        return is_null($token) ? $token : $token->getUser();
    }

    /**
     * Supprime les jetons expirés et retourne le nombre
     */
    public function removeExpired(): int
    {
        $tokens = $this
            ->createQueryBuilder('t')
            ->where('t.endAt < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();
        $count = 0;
        foreach ($tokens as $token) {
            $this->remove($token);
            $count++;
        }
        return $count;
    }
}
