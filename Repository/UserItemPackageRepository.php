<?php

namespace Ibtikar\TaniaModelBundle\Repository;

/**
 * UserItemPackageRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserItemPackageRepository extends \Doctrine\ORM\EntityRepository
{
    function insertOrIncreaseItemsCountsIfExist($order) {
        $orderItems = $order->getOrderItems();
        $user = $order->getUser();
        $em = $this->getEntityManager();
        /* @var $orderItem \Ibtikar\TaniaModelBundle\Entity\OrderItem */
        /* @var $userItemPackage \Ibtikar\TaniaModelBundle\Entity\UserItemPackage */
        foreach ($orderItems as $orderItem) {
            $item = $orderItem->getItem();
            $userItemPackage = $this->findOneBy(['user' => $user, 'item' => $item]);
            if ($userItemPackage) {
                $userItemPackage->setPurchasedCount($userItemPackage->getPurchasedCount() + $orderItem->getCount());
            } else {
                $userItemPackage = new \Ibtikar\TaniaModelBundle\Entity\UserItemPackage();
                $userItemPackage->setItem($orderItem->getItem());
                $userItemPackage->setPurchasedCount($orderItem->getCount());
                $userItemPackage->setUser($user);
                $userItemPackage->setRedeemedCount(0); // zero cuz it is a new record
            }
            $em->persist($userItemPackage);
        }
        $em->flush();
    }
}
