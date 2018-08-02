<?php

namespace Ibtikar\TaniaModelBundle\Service;

use Ibtikar\GoogleServicesBundle\Service\FirebaseCloudMessaging;
use Ibtikar\GoogleServicesBundle\Service\FireBaseRetrievingData;
use Ibtikar\TaniaModelBundle\Entity\DeviceNotification;
use Ibtikar\TaniaModelBundle\Entity\User;
use Doctrine\ORM\EntityManager;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class NotificationCenter
{

    const USER_ORDER_EDITED = 'user-order-edited';
    const USER_ORDER_ASSIGNED = 'user-order-assigned';
    const USER_ORDER_CLOSED = 'user-order-closed';
    const USER_ORDER_DELIVERING = 'user-order-delivering';
    const USER_ORDER_CANCEL = 'user-order-cancel';
    const USER_ORDER_DELIVERED = 'user-order-delivered';
    const USER_PAYMENT_FAILED = 'user-payment-failed';
    const USER_BALANCE_ORDER_ASSIGNED = 'user-balance-order-assigned';
    const USER_BALANCE_ORDER_CLOSED = 'user-blance-order-closed';
    const USER_BALANCE_ORDER_CONFIRMED = 'user-blance-order-confirmed';
    const DRIVER_ORDER_DELAYED = 'driver-order-delayed';

    /**
     * @var EntityManager $em
     */
    private $em;

    /**
     * @var UserDeviceNotification $userDeviceNotification
     */
    private $userDeviceNotification;

    /**
     * @var FireBaseRetrievingData $firebaseDatabase
     */
    private $firebaseDatabase;

    /**
     * @param EntityManager $em
     * @param FirebaseCloudMessaging $userDeviceNotification
     */
    public function __construct(EntityManager $em, UserDeviceNotification $userDeviceNotification,FireBaseRetrievingData $firebaseDatabase)
    {
        $this->em = $em;
        $this->userDeviceNotification = $userDeviceNotification;
        $this->firebaseDatabase = $firebaseDatabase;
    }

    /**
     * @param User $user
     * @param type $type
     * @param type $titleAr
     * @param type $titleEn
     * @param type $bodyAr
     * @param type $bodyEn
     * @param type $notificationLocale
     * @param array $data
     */
    public function sendNotificationToUser(User $user, $type, $titleAr, $titleEn, $bodyAr, $bodyEn, $notificationLocale = 'ar', array $data = array())
    {
        $title = $notificationLocale === 'ar' ? $titleAr : $titleEn;
        $body = $notificationLocale === 'ar' ? $bodyAr : $bodyEn;
        $this->userDeviceNotification->sendNotificationToUsers(array($user->getId()), $title, $body, $data);
        $deviceNotification = new DeviceNotification();
        $deviceNotification->setBodyAr($bodyAr);
        $deviceNotification->setBodyEn($bodyEn);
        $deviceNotification->setData($data);
        $deviceNotification->setTitleAr($titleAr);
        $deviceNotification->setTitleEn($titleEn);
        $deviceNotification->setType($type);
        $deviceNotification->setUser($user);
        $this->em->persist($deviceNotification);
        $this->em->flush();
    }

    public function sendNotificationToTopic($topic=null,$locale="ar",$titleAr, $titleEn, $bodyAr, $bodyEn,$oldStatus,$newStatus)
    {
        return $this->userDeviceNotification->sendNotificationToTopic($topic,$locale, $titleAr, $titleEn, $bodyAr, $bodyEn,$oldStatus,$newStatus);
    }

    public function updatefirebaseDatabase($order){

        
        $database=$this->firebaseDatabase->getDatabaseObject();
var_dump($database);
        $object=[
            "id"=>$order->getId(),
            "status"=>$order->getStatus()
        ];
        $database->getReference('Orders')
            ->update($object);


    }
}
