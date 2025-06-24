<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Trouve les utilisateurs par rôle
     */
    public function findByRole(string $role): array
    {
        $qb = $this->createQueryBuilder('u');
        $qb->where('JSON_CONTAINS(u.roles, :role) = 1')
           ->setParameter('role', json_encode($role));
        return $qb->getQuery()->getResult();
    }

    /**
     * Trouve tous les utilisateurs avec pagination
     */
    public function findAllPaginated(int $page = 1, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('u')
            ->orderBy('u.nom', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $countQb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)');

        return [
            'results' => $qb->getQuery()->getResult(),
            'totalItems' => (int)$countQb->getQuery()->getSingleScalarResult(),
        ];
    }

    /**
     * Trouve les utilisateurs actifs (avec dernière connexion récente)
     */
    public function findActiveUsers(\DateTimeInterface $since = null): array
    {
        $since = $since ?: new \DateTime('-30 days');
        
        return $this->createQueryBuilder('u')
            ->where('u.lastLogin >= :since')
            ->setParameter('since', $since)
            ->orderBy('u.lastLogin', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche des utilisateurs par nom ou email
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.nom LIKE :query OR u.email LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les utilisateurs par rôle
     */
    public function countByRole(): array
    {
        $users = $this->findAll();
        $counts = [
            'ROLE_SUPERADMIN' => 0,
            'ROLE_ADMIN' => 0,
            'ROLE_PHOTOGRAPHE' => 0,
        ];

        foreach ($users as $user) {
            $roles = $user->getRoles();
            if (in_array('ROLE_SUPERADMIN', $roles)) {
                $counts['ROLE_SUPERADMIN']++;
            } elseif (in_array('ROLE_ADMIN', $roles)) {
                $counts['ROLE_ADMIN']++;
            } elseif (in_array('ROLE_PHOTOGRAPHE', $roles)) {
                $counts['ROLE_PHOTOGRAPHE']++;
            }
        }

        return $counts;
    }
}