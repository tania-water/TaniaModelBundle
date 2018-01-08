<?php

namespace Ibtikar\TaniaModelBundle\Entity;

use Ibtikar\ShareEconomyPayFortBundle\Entity\PfTransactionInvoiceInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Ibtikar\ShareEconomyPayFortBundle\Entity\PfTransaction;
use Doctrine\ORM\Event\PreUpdateEventArgs;
/**
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="entityClass", type="string")
 * @ORM\Table(name="`order`")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="Ibtikar\TaniaModelBundle\Repository\OrderRepository")
 */
class Order implements PfTransactionInvoiceInterface
{
    use \Ibtikar\TaniaModelBundle\Entity\TrackableTrait;

    CONST CASH = 'CASH';
    CONST SADAD = 'SADAD';
    CONST BALANCE = 'BALANCE';
    CONST CREDIT = 'CREDIT';

    public static $paymentMethodList = array(
        self::CASH => 'Cash',
        self::SADAD => 'SADAD',
        self::CREDIT => 'Online With Card Id',
        self::BALANCE => 'Balance'
    );

    public static $statuses = array(
        'new' => 'new',
        'verified' => 'verified',
        'delivering' => 'delivering',
        'delivered' => 'delivered',
        'returned' => 'returned',
        'cancelled' => 'cancelled',
        'closed' => 'closed',
        'transaction-pending' => 'transaction-pending'
    );

    public static $statusCategories = array(
        'current'   => array('new', 'verified', 'delivering', 'returned'),
        'past'      => array('delivered', 'closed', 'transaction-pending', 'cancelled')
    );

    public static $disallowDeletePaymentStatuses = array('new', 'verified', 'delivering', 'transaction-pending');
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Ibtikar\TaniaModelBundle\Entity\User")
     */
    private $assignedBy;

    /**
     * @ORM\ManyToOne(targetEntity="\Ibtikar\TaniaModelBundle\Entity\User", inversedBy="orders")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="\Ibtikar\TaniaModelBundle\Entity\Driver", inversedBy="driverOrders")
     */
    protected $driver;

    /**
     * @ORM\ManyToOne(targetEntity="\Ibtikar\TaniaModelBundle\Entity\CityArea", inversedBy="orders")
     */
    protected $cityArea;

    /**
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @ORM\ManyToOne(targetEntity="\Ibtikar\TaniaModelBundle\Entity\Van")
     */
    protected $van;

    /**
     * @ORM\ManyToOne(targetEntity="\Ibtikar\TaniaModelBundle\Entity\City", inversedBy="orders")
     */
    protected $city;

    /**
     * @ORM\ManyToOne(targetEntity="\Ibtikar\TaniaModelBundle\Entity\Shift", inversedBy="orders")
     */
    protected $shift;

    /**
     * @var string
     *
     * @ORM\Column(name="`shift_from`", type="datetime")
     */
    private $shiftFrom;

    /**
     * @var string
     *
     * @ORM\Column(name="`shift_to`", type="datetime")
     */
    private $shiftTo;

    /**
     *
     * @ORM\OneToMany(targetEntity="\Ibtikar\TaniaModelBundle\Entity\OrderItem",mappedBy="order")
     */
    protected $orderItems;

    /**
     *
     * @ORM\OneToMany(targetEntity="\Ibtikar\TaniaModelBundle\Entity\OrderStatusHistory",mappedBy="order", cascade={"persist", "remove"})
     */
    protected $orderStatuses;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Ibtikar\ShareEconomyPayFortBundle\Entity\PfTransaction", mappedBy="invoice")
     */
    private $pfTransactions;

    /**
     * @var bool
     *
     * @ORM\Column(name="pfPayed", type="boolean", options={"default": false})
     */
    private $pfPayed = false;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_method", type="string")
     */
    private $paymentMethod;

    /**
     * @var text
     *
     * @ORM\Column(name="note", type="text", nullable=true)
     */
    private $note;

    /**
     * @var text
     *
     * @ORM\Column(name="close_reason", type="text", nullable=true)
     */
    private $closeReason;

    /**
     * @var string
     *
     * @ORM\Column(name="return_reason", type="string", length=190, nullable=true)
     */
    private $returnReason;


    /**
     * @var string
     *
     * @ORM\Column(name="cancel_reason", type="string", length=190, nullable=true)
     */
    private $cancelReason;

    /**
     * @var string
     *
     * @ORM\Column(name="cancel_date", type="datetime", nullable=true)
     */
    private $cancelDate;

    /**
     * @var string
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    private $endDate;

    /**
     * @var string
     *
     * @ORM\Column(name="skip_reason", type="string", length=190, nullable=true)
     */
    private $skipReason;

    /**
     * @var text
     *
     * @ORM\Column(name="receiving_date", type="text", nullable=true)
     */
    private $receivingDate;

    /**
     * @var Ibtikar\ShareEconomyPayFortBundle\Entity\PfPaymentMethod
     *
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @ORM\ManyToOne(targetEntity="Ibtikar\ShareEconomyPayFortBundle\Entity\PfPaymentMethod")
     */
    private $creditCard;

    /**
     * @var string
     *
     * @ORM\Column(name="fort_id", type="string", length=190, nullable=true)
     *
     */
    private $fortId;

    /**
     * @var string
     *
     * @ORM\Column(name="card_number", type="string", length=20, nullable=true)
     *
     */
    private $cardNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="expiry_date", type="string", length=10, nullable=true)
     *
     */
    private $expiryDate;

    /**
     * @var string
     *
     * @ORM\Column(name="merchant_reference", type="string", length=50, nullable=true)
     *
     */
    private $merchantReference;

    /**
     * @var string
     *
     * @ORM\Column(name="token_name", type="string", length=190, nullable=true)
     *
     */
    private $tokenName;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_option", type="string", length=50, nullable=true)
     */
    private $paymentOption;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_value", type="string", length=50, nullable=true)
     */
    private $paymentValue;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_default", type="boolean", options={"default": true})
     */
    private $isDefault = false;

    /**
     * @var string
     *
     * @ORM\Column(name="amountDue", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $amountDue;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2, options={"default": 0})
     * @Assert\Type(type="numeric")
     */
    protected $price;

    /**
     * @var string
     *
     * @ORM\Column(name="tax_fees", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $taxFees;

    /**
     * Set taxFees
     *
     * @param string $taxFees
     *
     * @return Order
     */
    public function setTaxFees($taxFees)
    {
        $this->taxFees = $taxFees;

        return $this;
    }

    /**
     * Get taxFees
     *
     * @return string
     */
    public function getTaxFees()
    {
        return $this->taxFees;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return Balance
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="rate", type="decimal", precision=4, scale=2, nullable=true,options={"comment":"value set by user for rating order"})
     * @Assert\NotBlank(message="fill_mandatory_field", groups={"rate"})
     * @Assert\Regex(
     *     pattern="/^[1-5]$/",
     *     match=true,
     *     message = "invalid_rate",
     *     groups = {"rate"}
     * )
     */
    private $rate;

    /**
     * @var string
     *
     * @ORM\Column(name="rate_comment", type="string", length=190, nullable=true,options={"comment":"value set by user for rating order"})
     * @Assert\Length(min = 3, max = 140, groups={"rate"}, maxMessage="comment_length_not_valid", minMessage="comment_length_not_valid", groups={"rate"})
     */
    private $rateComment;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=190)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="destination_verification_code", type="string", length=20, nullable=true)
     */
    private $destinationVerificationCode;

    /**
     * @var string
     *
     * @ORM\Column(name="destination_verification_code_counter", type="integer", length=20, nullable=true, options={"default": 0})
     */
    private $destinationVerificationCodeCounter = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="destination_verification_code_date", type="string", length=10, nullable=true)
     *
     */
    private $destinationVerificationCodeDate;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=100, nullable=true)
     * @Assert\Length(min = 4, max = 20, maxMessage="addressTitle_length_not_valid", minMessage="addressTitle_length_not_valid")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=400, nullable=true)
     * @Assert\Length(min = 4, max = 300, maxMessage="address_length_not_valid", minMessage="address_length_not_valid")
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="float", options={"default": 0}, nullable=true)
     */
    private $longitude;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="float", options={"default": 0}, nullable=true)
     */
    private $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="starting_longitude", type="float", options={"default": 0}, nullable=true)
     */
    private $startingLongitude;

    /**
     * @var string
     *
     * @ORM\Column(name="starting_latitude", type="float", options={"default": 0}, nullable=true)
     */
    private $startingLatitude;

    /**
     * @ORM\Column(name="van_number", type="string", nullable=true)
     */
    private $vanNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=100, nullable=true)
     *
     * @Assert\Length(min = 4, max = 12, groups={"username"}, maxMessage="username_length_not_valid", minMessage="username_length_not_valid")
     */
    private $driverUsername;


    /**
     * @var string
     *
     * @ORM\Column(name="driver_phone", type="string", length=100, nullable=true)
     *
     */
    private $driverPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="driver_fullName", type="string", length=190, nullable=true)
     *
     */
    protected $driverFullName;

    /**
     * @var string
     *
     * @ORM\Column(name="driver_fullNameAr", type="string", length=190, nullable=true)
     *
     */
    protected $driverFullNameAr;

    /**
     * @var string
     *
     * @ORM\Column(name="city_area_name_en", type="string", length=15, nullable=true)
     *
     */
    protected $cityAreaNameEn;

    /**
     * @var string
     *
     * @ORM\Column(name="city_area_name_ar", type="string", length=15, nullable=true)
     *
     */
    protected $cityAreaNameAr;

    /**
     * @var string
     *
     * @ORM\Column(name="driver_image", type="string", length=300, nullable=true)
     *
     */
    protected $driverImage;

    /**
     * @var string
     *
     * @ORM\Column(name="driver_rate", type="decimal", precision=4, scale=2, nullable=true)
     */
    private $driverRate;

    /**
     * @var string
     *
     * @ORM\Column(name="isSynced", type="boolean",  options={"default": 0}, nullable=true)
     */
    protected $isSynced;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->orderItems = new \Doctrine\Common\Collections\ArrayCollection();
        $this->orderStatuses = new \Doctrine\Common\Collections\ArrayCollection();
        $this->pfTransactions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setStatus($this::$statuses['new']);
    }

    /**
     * needed to disable the doctrine proxy __get as it trigger notice error
     * @param string $name
     */
    public function __get($name)
    {
        throw new \Exception("Variable $name was not found");
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set assignedBy
     *
     * @param \Ibtikar\TaniaModelBundle\Entity\User $assignedBy
     *
     * @return User
     */
    public function setAssignedBy(\Ibtikar\TaniaModelBundle\Entity\User $assignedBy = null)
    {
        $this->assignedBy = $assignedBy;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Ibtikar\TaniaModelBundle\Entity\User
     */
    public function getAssignedBy()
    {
        return $this->assignedBy;
    }

    /**
     * Set user
     *
     * @param \Ibtikar\TaniaModelBundle\Entity\User $user
     *
     * @return User
     */
    public function setUser(\Ibtikar\TaniaModelBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Ibtikar\TaniaModelBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add orderItem
     *
     * @param \Ibtikar\TaniaModelBundle\Entity\OrderItem $orderItem
     *
     * @return OrderItem
     */
    public function addOrderItem(\Ibtikar\TaniaModelBundle\Entity\OrderItem $orderItem)
    {
        $this->orderItems[] = $orderItem;

        return $this;
    }

    /**
     * Remove orderItem
     *
     * @param \Ibtikar\TaniaModelBundle\Entity\OrderItem $orderItem
     */
    public function removeOrderItem(\Ibtikar\TaniaModelBundle\Entity\OrderItem $orderItem)
    {
        $this->orderItems->removeElement($orderItem);
    }

    /**
     * Get orderItems
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrderItems()
    {
        return $this->orderItems;
    }

    /**
     * Set city
     *
     * @param \Ibtikar\TaniaModelBundle\Entity\City $city
     *
     * @return City
     */
    public function setCity(\Ibtikar\TaniaModelBundle\Entity\City $city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return \Ibtikar\TaniaModelBundle\Entity\City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set shift
     *
     * @param \Ibtikar\TaniaModelBundle\Entity\Shift $shift
     *
     * @return Shift
     */
    public function setShift(\Ibtikar\TaniaModelBundle\Entity\Shift $shift = null)
    {
        $this->shift = $shift;

        return $this;
    }

    /**
     * Get shift
     *
     * @return \Ibtikar\TaniaModelBundle\Entity\Shift
     */
    public function getShift()
    {
        return $this->shift;
    }

    /**
     * Set creditCard
     *
     * @param \Ibtikar\ShareEconomyPayFortBundle\Entity\PfPaymentMethod $creditCard
     *
     * @return Order
     */
    public function setCreditCard(\Ibtikar\ShareEconomyPayFortBundle\Entity\PfPaymentMethod $creditCard = null)
    {
        $this->creditCard = $creditCard;

        return $this;
    }

    /**
     * Get creditCard
     *
     * @return \Ibtikar\ShareEconomyPayFortBundle\Entity\PfPaymentMethod
     */
    public function getCreditCard()
    {
        return $this->creditCard;
    }

    /**
     * Set pfPayed
     *
     * @param boolean $pfPayed
     *
     * @return Order
     */
    public function setPfPayed($pfPayed)
    {
        $this->pfPayed = $pfPayed;

        return $this;
    }

    /**
     * Get pfPayed
     *
     * @return boolean
     */
    public function getPfPayed()
    {
        return $this->pfPayed;
    }


    /**
     * Add pfTransaction
     *
     * @param \Ibtikar\ShareEconomyPayFortBundle\Entity\PfTransaction $pfTransaction
     *
     * @return Order
     */
    public function addPfTransaction(\Ibtikar\ShareEconomyPayFortBundle\Entity\PfTransaction $pfTransaction)
    {
        $this->pfTransactions[] = $pfTransaction;

        return $this;
    }

    /**
     * Remove pfTransaction
     *
     * @param \Ibtikar\ShareEconomyPayFortBundle\Entity\PfTransaction $pfTransaction
     */
    public function removePfTransaction(\Ibtikar\ShareEconomyPayFortBundle\Entity\PfTransaction $pfTransaction)
    {
        $this->pfTransactions->removeElement($pfTransaction);
    }

    /**
     * Get pfTransactions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPfTransactions()
    {
        return $this->pfTransactions;
    }

    /**
     * @return Ibtikar\ShareEconomyPayFortBundle\Entity\PfPaymentMethod
     */
    public function getPfPaymentMethod()
    {
        return $this->creditCard;
    }

    /**
     * Set paymentMethod
     *
     * @param string $paymentMethod
     *
     * @return Order
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * Get paymentMethod
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * Set note
     *
     * @param string $note
     *
     * @return Order
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set receivingDate
     *
     * @param string $receivingDate
     *
     * @return Order
     */
    public function setReceivingDate($receivingDate)
    {
        $this->receivingDate = $receivingDate;

        return $this;
    }

    /**
     * Get receivingDate
     *
     * @return string
     */
    public function getReceivingDate()
    {
        return $this->receivingDate;
    }

    /**
     * Set amountDue
     *
     * @param string $amountDue
     *
     * @return RequestOrder
     */
    public function setAmountDue($amountDue)
    {
        $this->amountDue = $amountDue;

        return $this;
    }

    /**
     * Get amountDue
     *
     * @return string
     */
    public function getAmountDue()
    {
        return $this->amountDue;
    }

    /**
     * Set rate
     *
     * @param string $rate
     *
     * @return Order
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * Get rate
     *
     * @return string
     */
    public function getRate()
    {
        return $this->rate + 0;
    }

    /**
     * Set rateComment
     *
     * @param string $rateComment
     *
     * @return RequestOrder
     */
    public function setRateComment($rateComment)
    {
        $this->rateComment = $rateComment;

        return $this;
    }

    /**
     * Get rateComment
     *
     * @return string
     */
    public function getRateComment()
    {
        return $this->rateComment;
    }

    /**
     * Set driver
     *
     * @param \Ibtikar\TaniaModelBundle\Entity\Driver $driver
     *
     * @return Driver
     */
    public function setDriver(\Ibtikar\TaniaModelBundle\Entity\Driver $driver = null)
    {
        $this->driver = $driver;
        $this->setDriverFullName($driver->getFullName());
        $this->setDriverFullNameAr($driver->getFullNameAr());
        if ($driver->getImage()) {
            $this->setDriverImage($driver->getWebPath());
        }
        $this->setDriverPhone($driver->getPhone());
        $this->setDriverRate($driver->getDriverRate());
        $this->setDriverUsername($driver->getUsername());
        foreach ($driver->getVanDrivers() as $vanDriver) {
            $this->setVan($vanDriver->getVan());
        }

        return $this;
    }

    /**
     * Get driver
     *
     * @return \Ibtikar\TaniaModelBundle\Entity\Driver
     */
    public function getDriver()
    {
        return $this->driver;
    }


    /**
     * Set status
     *
     * @param string $status
     *
     * @return Order
     */
    public function setStatus($status)
    {
        if ($status != $this->status) {
            $orderStatus = new OrderStatusHistory();
            $orderStatus->setStatus($status);
            $orderStatus->setOrder($this);
            $this->addOrderStatus($orderStatus);
        }
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set van
     *
     * @param \Ibtikar\TaniaModelBundle\Entity\Van $van
     *
     * @return Van
     */
    public function setVan(\Ibtikar\TaniaModelBundle\Entity\Van $van = null)
    {
        $this->van = $van;
        $this->setVanNumber($van->getVanNumber());

        return $this;
    }

    /**
     * Get driver
     *
     * @return \Ibtikar\TaniaModelBundle\Entity\Van
     */
    public function getVan()
    {
        return $this->van;
    }

    public function getStatuses()
    {
        return array_flip(self::$statuses);
    }

    public function getUserName()
    {
        if ($this->user)
            return $this->user->getFullName();

        return '';
    }

    public function getUserPhone()
    {
        if ($this->user)
            return $this->user->getPhone();

        return '';
    }

    /**
     * Set closeReason
     *
     * @param string $closeReason
     *
     * @return Order
     */
    public function setCloseReason($closeReason)
    {
        $this->closeReason = $closeReason;

        return $this;
    }

    /**
     * Get closeReason
     *
     * @return string
     */
    public function getCloseReason()
    {
        return $this->closeReason;
    }

    /**
     * Set destinationVerificationCode
     *
     * @param string $destinationVerificationCode
     *
     * @return RequestOrder
     */
    public function setDestinationVerificationCode($destinationVerificationCode)
    {
        $this->destinationVerificationCode = $destinationVerificationCode;

        return $this;
    }

    /**
     * Get destinationVerificationCode
     *
     * @return string
     */
    public function getDestinationVerificationCode()
    {
        return $this->destinationVerificationCode;
    }

    /**
     * Set shiftFrom
     *
     * @param string $shiftFrom
     *
     * @return ShiftFrom
     */
    public function setShiftFrom($shiftFrom)
    {
        $this->shiftFrom = $shiftFrom;

        return $this;
    }

    /**
     * Get shiftFrom
     *
     * @return string
     */
    public function getShiftFrom()
    {
        return $this->shiftFrom;
    }

    /**
     * Set shiftTo
     *
     * @param string $shiftTo
     *
     * @return ShiftTo
     */
    public function setShiftTo($shiftTo)
    {
        $this->shiftTo = $shiftTo;

        return $this;
    }

    /**
     * Get shiftTo
     *
     * @return string
     */
    public function getShiftTo()
    {
        return $this->shiftTo;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return UserAddress
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     *
     * @return UserAddress
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     *
     * @return UserAddress
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set startingLongitude
     *
     * @param string $startingLongitude
     *
     * @return UserAddress
     */
    public function setStartingLongitude($startingLongitude)
    {
        $this->startingLongitude = $startingLongitude;

        return $this;
    }

    /**
     * Get startingLongitude
     *
     * @return string
     */
    public function getStartingLongitude()
    {
        return $this->startingLongitude;
    }

    /**
     * Set startingLatitude
     *
     * @param string $startingLatitude
     *
     * @return UserAddress
     */
    public function setStartingLatitude($startingLatitude)
    {
        $this->startingLatitude = $startingLatitude;

        return $this;
    }

    /**
     * Get startingLatitude
     *
     * @return string
     */
    public function getStartingLatitude()
    {
        return $this->startingLatitude;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return UserAddress
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function getDriverUsername()
    {
        return $this->driverUsername;
    }

    public function setDriverUsername($driverUsername)
    {
        $this->driverUsername = $driverUsername;

        return $this;
    }

    /**
     * Set vanNumber
     *
     * @param string $vanNumber
     *
     * @return Van
     */
    public function setVanNumber($vanNumber)
    {
        $this->vanNumber = $vanNumber;

        return $this;
    }

    /**
     * Get vanNumber
     *
     * @return string
     */
    public function getVanNumber()
    {
        return $this->vanNumber;
    }

    /**
     * Set cardNumber
     *
     * @param string $cardNumber
     *
     * @return Order
     */
    public function setCardNumber($cardNumber)
    {
        $this->cardNumber = $cardNumber;

        return $this;
    }

    /**
     * Get cardNumber
     *
     * @return string
     */
    public function getCardNumber()
    {
        return $this->cardNumber;
    }

    /**
     * Set expiryDate
     *
     * @param string $expiryDate
     *
     * @return Order
     */
    public function setExpiryDate($expiryDate)
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    /**
     * Get expiryDate
     *
     * @return string
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * Set merchantReference
     *
     * @param string $merchantReference
     *
     * @return Order
     */
    public function setMerchantReference($merchantReference)
    {
        $this->merchantReference = $merchantReference;

        return $this;
    }

    /**
     * Get merchantReference
     *
     * @return string
     */
    public function getMerchantReference()
    {
        return $this->merchantReference;
    }

    /**
     * Set tokenName
     *
     * @param string $tokenName
     *
     * @return Order
     */
    public function setTokenName($tokenName)
    {
        $this->tokenName = $tokenName;

        return $this;
    }

    /**
     * Get tokenName
     *
     * @return string
     */
    public function getTokenName()
    {
        return $this->tokenName;
    }

    /**
     * Set paymentOption
     *
     * @param string $paymentOption
     *
     * @return Order
     */
    public function setPaymentOption($paymentOption)
    {
        $this->paymentOption = $paymentOption;

        return $this;
    }

    /**
     * Get paymentOption
     *
     * @return string
     */
    public function getPaymentOption()
    {
        return $this->paymentOption;
    }

    /**
     * Set paymentValue
     *
     * @param string $paymentValue
     *
     * @return Order
     */
    public function setPaymentValue($paymentValue)
    {
        $this->paymentValue = $paymentValue;

        return $this;
    }

    /**
     * Get paymentValue
     *
     * @return string
     */
    public function getPaymentValue()
    {
        return $this->paymentValue;
    }

    /**
     * Set isDefault
     *
     * @param boolean $isDefault
     *
     * @return Order
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Get isDefault
     *
     * @return boolean
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Set fortId
     *
     * @param string $fortId
     *
     * @return Order
     */
    public function setFortId($fortId)
    {
        $this->fortId = $fortId;

        return $this;
    }

    /**
     * Get fortId
     *
     * @return string
     */
    public function getFortId()
    {
        return $this->fortId;
    }

    /**
     * Set driverPhone
     *
     * @param string $driverPhone
     *
     * @return Order
     */
    public function setDriverPhone($driverPhone)
    {
        $this->driverPhone = $driverPhone;

        return $this;
    }

    /**
     * Get driverPhone
     *
     * @return string
     */
    public function getDriverPhone()
    {
        return $this->driverPhone;
    }

    /**
     * Set driverFullName
     *
     * @param string $driverFullName
     *
     * @return Order
     */
    public function setDriverFullName($driverFullName)
    {
        $this->driverFullName = $driverFullName;

        return $this;
    }

    /**
     * Get driverFullName
     *
     * @return string
     */
    public function getDriverFullName()
    {
        return $this->driverFullName;
    }

    /**
     * Set driverFullNameAr
     *
     * @param string $driverFullNameAr
     *
     * @return Order
     */
    public function setDriverFullNameAr($driverFullNameAr)
    {
        $this->driverFullNameAr = $driverFullNameAr;

        return $this;
    }

    /**
     * Get driverFullNameAr
     *
     * @return string
     */
    public function getDriverFullNameAr()
    {
        return $this->driverFullNameAr;
    }

    /**
     * Set driverImage
     *
     * @param string $driverImage
     *
     * @return Order
     */
    public function setDriverImage($driverImage)
    {
        $this->driverImage = $driverImage;

        return $this;
    }

    /**
     * Get driverImage
     *
     * @return string
     */
    public function getDriverImage()
    {
        return $this->driverImage;
    }

    /**
     * Set driverRate
     *
     * @param string $driverRate
     *
     * @return Order
     */
    public function setDriverRate($driverRate)
    {
        $this->driverRate = $driverRate;

        return $this;
    }

    /**
     * Get driverRate
     *
     * @return string
     */
    public function getDriverRate()
    {
        return $this->driverRate;
    }

    /**
     * Set cityArea
     *
     * @param \Ibtikar\TaniaModelBundle\Entity\CityArea $cityArea
     *
     * @return Order
     */
    public function setCityArea(\Ibtikar\TaniaModelBundle\Entity\CityArea $cityArea = null)
    {
        $this->cityArea = $cityArea;

        return $this;
    }

    /**
     * Get cityArea
     *
     * @return \Ibtikar\TaniaModelBundle\Entity\CityArea
     */
    public function getCityArea()
    {
        return $this->cityArea;
    }

    /**
     * Set destinationVerificationCodeCounter
     *
     * @param integer $destinationVerificationCodeCounter
     *
     * @return Order
     */
    public function setDestinationVerificationCodeCounter($destinationVerificationCodeCounter)
    {
        $this->destinationVerificationCodeCounter = $destinationVerificationCodeCounter;

        return $this;
    }

    /**
     * Get destinationVerificationCodeCounter
     *
     * @return integer
     */
    public function getDestinationVerificationCodeCounter()
    {
        return $this->destinationVerificationCodeCounter;
    }

    /**
     * Set destinationVerificationCodeDate
     *
     * @param string $destinationVerificationCodeDate
     *
     * @return Order
     */
    public function setDestinationVerificationCodeDate($destinationVerificationCodeDate)
    {
        $this->destinationVerificationCodeDate = $destinationVerificationCodeDate;

        return $this;
    }

    /**
     * Get destinationVerificationCodeDate
     *
     * @return string
     */
    public function getDestinationVerificationCodeDate()
    {
        return $this->destinationVerificationCodeDate;
    }

    /**
     * Set returnReason
     *
     * @param string $returnReason
     *
     * @return Order
     */
    public function setReturnReason($returnReason)
    {
        $this->returnReason = $returnReason;

        return $this;
    }

    /**
     * Get returnReason
     *
     * @return string
     */
    public function getReturnReason()
    {
        return $this->returnReason;
    }

    /**
     * Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @return null|integer
     */
    public function getCityAreaId()
    {
        if ($this->getCityArea()) {
            return $this->getCityArea()->getId();
        }
    }

    /**
     * @return boolean
     */
    public function isOrderAssignableToOfflineDrivers()
    {
        if ($this->getShiftFrom() && $this->getShiftTo()) {
            $orderShiftStartTime = new \DateTime($this->getShiftFrom()->format('H:i:s'));
            $orderShiftEndTime = new \DateTime($this->getShiftTo()->format('H:i:s'));
            try {
                $orderDate = new \DateTime('@' . $this->getReceivingDate());
                $currentTime = new \DateTime();
                if ($orderDate->format('d') === $currentTime->format('d') && $currentTime >= $orderShiftStartTime && $currentTime <= $orderShiftEndTime) {
                    return false;
                }
            } catch (\Exception $e) {

            }
        }
        return true;
    }

    /**
     * check if it is possible to create new transaction for this invoice or not
     * @return boolean
     */
    public function canCreateNewTransaction()
    {
        $return = true;

        if ($this->getPfTransactions()) {
            foreach ($this->getPfTransactions() as $transaction) {
                if ($transaction->getCurrentStatus() != PfTransaction::STATUS_FAIL) {
                    $return = false;
                    break;
                }
            }
        }

        return $return;
    }

    /**
     * Set applicationLanguage
     *
     * @param string $isSynced
     *
     * @return User
     */
    public function setIsSynced($isSynced)
    {
        $this->isSynced = $isSynced;

        return $this;
    }

    /**
     * Get $isSynced
     *
     * @return string
     */
    public function getIsSynced()
    {
        return $this->isSynced;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function prePersist($event)
    {
        if ($event instanceof  PreUpdateEventArgs ) {
            if ( $event->hasChangedField('isAsynced') == true ) {
                $this->isSynced= false;
            }
        }
        else
        {
            $this->isSynced= false;
        }
    }


    /**
     * Set returnReason
     *
     * @param string $returnReason
     *
     * @return Order
     */
    public function setSkipReason($skipReason)
    {
        $this->skipReason = $skipReason;

        return $this;
    }

    /**
     * Get returnReason
     *
     * @return string
     */
    public function getSkipReason()
    {
        return $this->skipReason;
    }

    /**
     * Set cancelReason
     *
     * @param string $cancelReason
     *
     * @return Order
     */
    public function setCancelReason($cancelReason)
    {
        $this->cancelReason = $cancelReason;

        return $this;
    }

    /**
     * Get cancelReason
     *
     * @return string
     */
    public function getCancelReason()
    {
        return $this->cancelReason;
    }

    /**
     * Set cancelDate
     *
     * @param string $cancelDate
     *
     * @return Order
     */
    public function setCancelDate($cancelDate)
    {
        $this->cancelDate = $cancelDate;

        return $this;
    }

    /**
     * Get cancelDate
     *
     * @return string
     */
    public function getCancelDate()
    {
        return $this->cancelDate;
    }

    public function getBalanceRequestStatuses()
    {
        $statuses = self::$statuses;
        unset($statuses['cancelled']);
        unset($statuses['transaction-pending']);
        return array_flip($statuses);
    }

    /**
     * Add orderStatus
     *
     * @param \Ibtikar\TaniaModelBundle\Entity\OrderStatusHistory $orderStatus
     *
     * @return OrderStatusHistory
     */
    public function addOrderStatus(\Ibtikar\TaniaModelBundle\Entity\OrderStatusHistory $orderStatus)
    {
        $this->orderStatuses[] = $orderStatus;

        return $this;
    }

    /**
     * Remove orderStatus
     *
     * @param \Ibtikar\TaniaModelBundle\Entity\OrderStatusHistory $orderStatus
     */
    public function removeOrderStatus(\Ibtikar\TaniaModelBundle\Entity\OrderStatusHistory $orderStatus)
    {
        $this->orderStatuses->removeElement($orderStatus);
    }

    /**
     * Get $orderStatuses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrderStatuses()
    {
        return $this->$orderStatuses;
    }

    public function getPaymentMethods()
    {
        return self::$paymentMethodList;
    }

    public function getRateValues()
    {
        return [1,2,3,4,5];
    }

    /**
     * Get cityNameAr
     *
     * @return string
     */
    public function getCityNameAr()
    {
        return $this->city ? $this->city->getNameAr(): '';
    }

    /**
     * Get cityNameEn
     *
     * @return string
     */
    public function getCityNameEn()
    {
        return $this->city ? $this->city->getNameEn(): '';
    }


    /**
     * Set cityAreaNameEn
     *
     * @param string $cityAreaNameEn
     *
     * @return Order
     */
    public function setCityAreaNameEn($cityAreaNameEn)
    {
        $this->cityAreaNameEn = $cityAreaNameEn;

        return $this;
    }

    /**
     * Get cityAreaNameEn
     *
     * @return string
     */
    public function getCityAreaNameEn()
    {
        return $this->cityAreaNameEn;
    }

    /**
     * Set cityAreaNameAr
     *
     * @param string $cityAreaNameAr
     *
     * @return Order
     */
    public function setCityAreaNameAr($cityAreaNameAr)
    {
        $this->cityAreaNameAr = $cityAreaNameAr;

        return $this;
    }

    /**
     * Get cityAreaNameAr
     *
     * @return string
     */
    public function getCityAreaNameAr()
    {
        return $this->cityAreaNameAr;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return Order
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime|null
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @return string
     */
    public function getItemsNamesAndQuantities()
    {
        $itemsString = '';
        foreach ($this->orderItems as $orderItem) {
            if (strlen($itemsString) !== 0) {
                $itemsString .= '<br/> ';
            }
            $itemsString .= $orderItem->getItem()->getNameEn() . ': ' . $orderItem->getCount();
        }
        return $itemsString;
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @return Integer
     */
    public function getItemsCount()
    {
        $itemsCount = 0;
        foreach ($this->orderItems as $orderItem) {
            $itemsCount += $orderItem->getCount();
        }
        return $itemsCount;
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @return string
     */
    public function getEndDateString()
    {
        $endDateString = '';
        if ($this->endDate) {
            $endDateString = $this->endDate->format('d/m/Y');
        }
        return $endDateString;
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @return string
     */
    public function getTotalSpentTimeAr()
    {
        $time = '';
        if ($this->endDate && $this->createdAt) {
            $timeInterval = $this->endDate->diff($this->createdAt);
            $minuteString = 'دقيقة';
            $hourString = 'ساعة';
            $dayString = 'يوم';
            $time = $timeInterval->format("%a $dayString, %H $hourString, %I $minuteString");
        }
        return $time;
    }

    /**
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @return string
     */
    public function getTotalSpentTimeEn()
    {
        $time = '';
        if ($this->endDate && $this->createdAt) {
            $timeInterval = $this->endDate->diff($this->createdAt);
            $minuteString = 'minute';
            $hourString = 'hour';
            $dayString = 'day';
            $time = $timeInterval->format("%a $dayString, %H $hourString, %I $minuteString");
        }
        return $time;
    }
}
