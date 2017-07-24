<?php

namespace Ibtikar\TaniaModelBundle\Repository;

use \Doctrine\ORM\EntityRepository;

class ItemRepository extends EntityRepository
{
    function getItemsNotAssignedToVan($vanId)
    {
        return $this->createQueryBuilder('i')
            ->select('i, vanItems.id')
            ->leftJoin('i.vanItems', 'vanItems','WITH','vanItems.van = :vanId')
            ->andWhere('vanItems.id is NULL')
            ->setParameter(':vanId', $vanId)
            ->getQuery()
            ->getResult();

    }

    function getVanItems($vanId)
    {
        return $this->createQueryBuilder('i')
            ->select('i')
            ->innerJoin('i.vanItems', 'vanItems','WITH','vanItems.van = :vanId')
            ->setParameter(':vanId', $vanId)
            ->andWhere('i.shown = 1')
            ->getQuery()
            ->getResult();

    }
}