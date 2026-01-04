<?php

namespace Connector\Repository;

use Connector\Entity\LinkContentEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LinkContentEntity>
 */
class LinkContentEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LinkContentEntity::class);
    }

    public function findByGuid(string $guid): ?LinkContentEntity
    {
        return $this->findOneBy(['guid' => $guid]);
    }

    //    /**
    //     * @return PodcastEpisodeEntity[] Returns an array of PodcastEpisodeEntity objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    public function findOneByStatusField($status, $guid): ?LinkContentEntity
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->andWhere('p.guid = :guid')
            ->setParameter('status', $status)
            ->setParameter('guid', $guid)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
