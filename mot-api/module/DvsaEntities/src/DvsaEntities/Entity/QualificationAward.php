<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * MotTest.
 *
 * @ORM\Table(name="qualification_award")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\QualificationAwardRepository")
 */
class QualificationAward extends Entity
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
     * @var \DvsaEntities\Entity\Site
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Site")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     */
    private $site;

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
     * @ORM\Column(name="date_of_qualification", type="datetime", nullable=false)
     */
    private $dateOfQualification;

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Person $person
     *
     * @return QualificationAward
     */
    public function setPerson(Person $person)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * @return Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param Site $site
     *
     * @return QualificationAward
     */
    public function setSite(Site $site = null)
    {
        $this->site = $site;

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
     *
     * @return QualificationAward
     */
    public function setCertificateNumber($certificateNumber)
    {
        $this->certificateNumber = $certificateNumber;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateOfQualification()
    {
        return $this->dateOfQualification;
    }

    /**
     * @param \DateTime $dateOfQualification
     *
     * @return QualificationAward
     */
    public function setDateOfQualification(\DateTime $dateOfQualification)
    {
        $this->dateOfQualification = $dateOfQualification;

        return $this;
    }
}
