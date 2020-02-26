<?php

namespace App\Repository;

use App\Entity\ServerLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ServerLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method ServerLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method ServerLog[]    findAll()
 * @method ServerLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServerLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServerLog::class);
    }


    public function findAllBySearch(ServerLog $serverLog)
    {
        $qb = $this->createQueryBuilder('s');

        if ($serverLog->getId()) {
            $qb->andWhere('s.id = :id')
               ->setParameter('id', $serverLog->getId());
        }

        if ($serverLog->getStatus()) {
            $qb->andWhere('s.status LIKE :status')
                ->setParameter('status', '%' . $serverLog->getStatus() . '%');
        }

        if ($serverLog->getServer()) {
            $qb->andWhere('s.server LIKE :server')
                ->setParameter('server', '%' . $serverLog->getServer() . '%');
        }

        if ($dateLog = $serverLog->getDateLog()) {
            $qb->andWhere('s.dateLog >= :dateLogFrom AND s.dateLog <= :dateLogTo')
                ->setParameter('dateLogFrom', $dateLog->format("Y-m-d 00:00:00"))
                ->setParameter('dateLogTo', $dateLog->format("Y-m-d 23:59:59"));
        }

        if ($dateAdded = $serverLog->getDateAdded()) {
            $qb->andWhere('s.dateAdded >= :dateAddedFrom AND s.dateAdded <= :dateAddedTo')
                ->setParameter('dateAddedFrom', $dateAdded->format("Y-m-d 00:00:00"))
                ->setParameter('dateAddedTo', $dateAdded->format("Y-m-d 23:59:59"));
        }

        return $qb->getQuery()->getResult();
    }
}
