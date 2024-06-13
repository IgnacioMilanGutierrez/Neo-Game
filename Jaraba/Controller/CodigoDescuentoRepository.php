<?php
namespace App\Repository;

use App\Entity\CodigoDescuento;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CodigoDescuentoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CodigoDescuento::class);
    }

    public function findCodigoDescuento(string $codigo)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.codigo = :codigo')
            ->andWhere('d.fechaCaducidad > :now')
            ->setParameter('codigo', $codigo)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
