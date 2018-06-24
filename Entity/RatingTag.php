<?php

namespace Ibtikar\TaniaModelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * RatingTag
 *
 * @ORM\Table(name="rating_tag")
 * @ORM\Entity(repositoryClass="Ibtikar\TaniaModelBundle\Repository\RatingTagRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class RatingTag
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="name_en", type="string", length=255)
     */
    private $nameEn;
    
    /**
     * @ORM\OneToMany(targetEntity="\Ibtikar\TaniaModelBundle\Entity\RatingTagRatingRange", mappedBy="ratingTag")
     */
    private $ratingTagRatingRanges;

    /**
     * @var \DateTime $deletedAt
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    private $deletedAt;

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
     * Set name
     *
     * @param string $name
     *
     * @return RatingTag
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set nameEn
     *
     * @param string $nameEn
     *
     * @return RatingTag
     */
    public function setNameEn($nameEn)
    {
        $this->nameEn = $nameEn;

        return $this;
    }

    /**
     * Get nameEn
     *
     * @return string
     */
    public function getNameEn()
    {
        return $this->nameEn;
    }

    public function getRatingTagRatingRanges() 
    {
        return $this->ratingTagRatingRanges;
    }

    public function setRatingTagRatingRanges($ratingTagRatingRanges) 
    {
        $this->ratingTagRatingRanges = $ratingTagRatingRanges;
        return $this;
    }
    
    /**    
     * @return RatingTag
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }
}

