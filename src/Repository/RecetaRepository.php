<?php

namespace App\Repository;

use App\Entity\Receta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Receta>
 */
class RecetaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Receta::class);
    }

    public function findRecetasByNombre(string $nombre): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.nombre LIKE :nombre')
            ->setParameter('nombre', '%' . $nombre . '%') 
            ->getQuery()
            ->getResult();
    }

    public function findRecetasByIngredientes(array $ingredientes): array
    {
        $qb = $this->createQueryBuilder('r');

        // Unir con la tabla de ingredientes
        $qb->leftJoin('r.ingredientes', 'i');

        // Filtrar recetas que contienen los ingredientes seleccionados
        $qb->andWhere($qb->expr()->in('i.nombre', ':ingredientes'))
           ->setParameter('ingredientes', $ingredientes);

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Receta[] Returns an array of Receta objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Receta
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }


    
}
