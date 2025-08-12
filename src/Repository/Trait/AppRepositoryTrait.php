<?php

namespace App\Repository\Trait;

use Doctrine\ORM\QueryBuilder;

trait AppRepositoryTrait
{
    /**
     * Définit la locale de la connexion à la base de données
     * 
     * @param ?string $locale Locale à utiliser (fr_FR, en_US...)
     */
    public function setLocale(?string $locale = null): void
    {
        $this->query(sprintf('SET lc_time_names=\'%s\'', is_null($locale) ? 'fr_FR' : $locale));
    }

    /**
     * Exécute une requête SQL
     * 
     * @param string $sql Script SQL à exécuter
     * @param ?array $params Paramètres du script
     * @param ?bool $fetchOne Retourner un seul enregistrement
     */
    public function query(string $sql, ?array $params = [], ?bool $fetchOne = false): array
    {
        $stmt = $this->getEntityManager()->getConnection()->executeQuery($sql, $params);
        return $fetchOne ? $stmt->fetchAssociative() : $stmt->fetchAllAssociative();
    }

    /**
     * Retourne le QueryBuilder de sélection d'entités aléatoires
     * 
     * @param string $alias Alias de la table
     * @param ?string $criteria Critères de sélection
     * @param ?int $limit Nombre d'entité à retourner
     */
    public function findRandQb(string $alias, ?string $criteria = null, ?int $limit = 1): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder($alias)
            ->setMaxResults($limit)
            ->orderBy('RAND()');

        if (!is_null($criteria)) {
            $qb->where($criteria);
        }

        return $qb;
    }

    /**
     * Retourne une ou plusieurs entités aléatoires
     * 
     * @param string $alias Alias de la table
     * @param ?string $criteria Critères de sélection
     * @param ?int $limit Nombre d'entité à retourner
     */
    public function findRand(string $alias, ?string $criteria = null, ?int $limit = 1): array|object|null
    {
        $query = $this->findRandQb($alias, $criteria, $limit)->getQuery();
        if ($limit === 1) {
            return $query->getOneOrNullResult();
        }
        return $query->getResult();
    }

    /**
     * Sauvegarde une entité et la retourne
     * 
     * @param object $entity Entité à sauvegarder
     * @param ?bool $flush Mettre à jour la base de données
     */
    public function save(object $entity, ?bool $flush = true): object
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
        return $entity;
    }

    /**
     * Supprime une entité
     * 
     * @param object $entity Entité à supprimer
     * @param ?bool $flush Mettre à jour la base de données
     */
    public function remove(object $entity, ?bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retourne la dernière entité créée
     */
    public function getLastInsert(): object|null
    {
        return $this
            ->createQueryBuilder('t')
            ->setMaxResults(1)
            ->orderBy('t.id', 'DESC')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
