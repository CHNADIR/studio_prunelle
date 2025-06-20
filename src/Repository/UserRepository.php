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

    public function findByRole(string $role): array
    {
        $qb = $this->createQueryBuilder('u');
        $qb->where('JSON_CONTAINS(u.roles, :role) = 1')
           ->setParameter('role', json_encode($role));
        return $qb->getQuery()->getResult();
    }
}