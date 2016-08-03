<?php

namespace DvsaEntities\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * QualificationAnnualCertificate
 *
 * @ORM\Table(name="qualification_annual_certificate")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\QualificationAnnualCertificateRepository")
 */
class QualificationAnnualCertificate extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var \DvsaEntities\Entity\Person
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     */
    private $person;

    /**
     * @var \DvsaEntities\Entity\VehicleClassGroup
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\VehicleClassGroup")
     * @ORM\JoinColumn(name="vehicle_class_group_id", referencedColumnName="id")
     */
    private $vehicleClassGroup;

    /**
     * @var string
     *
     * @ORM\Column(name="certificate_number", type="string", nullable=false)
     */
    private $certificateNumber;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_awarded", type="datetime", nullable=false)
     */
    private $dateAwarded;

    /**
     * @var int;
     *
     * @ORM\Column(name="score", type="integer", nullable=false)
     */
    private $score;

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param  Person $person
     * @return QualificationAnnualCertificate
     */
    public function setPerson(Person $person)
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return VehicleClassGroup
     */
    public function getVehicleClassGroup()
    {
        return $this->vehicleClassGroup;
    }

    public function setVehicleClassGroup(VehicleClassGroup $vehicleClassGroup)
    {
        $this->vehicleClassGroup = $vehicleClassGroup;
        return $this;
    }

    /**
     * @return string
     */
    public function getCertificateNumber()
    {
        return $this->certificateNumber;
    }

    /**
     * @param $certificateNumber
     * @return QualificationAnnualCertificate
     */
    public function setCertificateNumber($certificateNumber)
    {
        $this->certificateNumber = $certificateNumber;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateAwarded()
    {
        return $this->dateAwarded;
    }

    /**
     * @param \DateTime $dateAwarded
     * @return QualificationAnnualCertificate
     */
    public function setDateAwarded(\DateTime $dateAwarded)
    {
        $this->dateAwarded = $dateAwarded;
        return $this;
    }

    /**
     * @return int
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @param int $score
     * @return QualificationAnnualCertificate
     */
    public function setScore($score)
    {
        $this->score = $score;
        return $this;
    }
}
