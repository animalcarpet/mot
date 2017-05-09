<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * AuthorisationForAuthorisedExaminer.
 *
 * @ORM\Table(name="auth_for_ae")
 * @ORM\Entity(repositoryClass="\DvsaEntities\Repository\AuthorisationForAuthorisedExaminerRepository")
 */
class AuthorisationForAuthorisedExaminer extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var \DvsaEntities\Entity\Organisation
     *
     * @ORM\OneToOne(targetEntity="\DvsaEntities\Entity\Organisation", inversedBy="authorisedExaminer", fetch="LAZY")
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id", nullable=false)
     */
    private $organisation;

    /**
     * @var string
     *
     * @ORM\Column(name="ae_ref", type="string", length=12, nullable=true)
     */
    private $number;

    /**
     * @var \DvsaEntities\Entity\AuthForAeStatus
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\AuthForAeStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     * })
     */
    private $status;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="status_changed_on", type="datetime", nullable=false)
     */
    private $statusChangedOn;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="valid_from", type="datetime", nullable=false)
     */
    private $validFrom;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="expiry_date", type="datetime", nullable=true)
     */
    private $expiryDate;

    /**
     * @var Site
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Site")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ao_site_id", referencedColumnName="id")
     * })
     */
    private $areaOffice;

    /**
     * @param $organisation
     *
     * @return AuthorisationForAuthorisedExaminer
     */
    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * @return Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @return Person
     */
    public function getDesignatedManager()
    {
        return $this->organisation->getDesignatedManager();
    }

    /**
     * @param $number
     *
     * @return AuthorisationForAuthorisedExaminer
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param \DvsaEntities\Entity\AuthForAeStatus $status
     *
     * @return AuthorisationForAuthorisedExaminer
     */
    public function setStatus(AuthForAeStatus $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return AuthForAeStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param \DateTime|null $value
     *
     * @return AuthorisationForAuthorisedExaminer
     */
    public function setStatusChangedOn(\DateTime $value)
    {
        $this->statusChangedOn = $value;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getStatusChangedOn()
    {
        return $this->statusChangedOn;
    }

    /**
     * @param \DateTime|null $value
     *
     * @return AuthorisationForAuthorisedExaminer
     */
    public function setValidFrom(\DateTime $value)
    {
        $this->validFrom = $value;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * @param \DateTime|null $value
     *
     * @return AuthorisationForAuthorisedExaminer
     */
    public function setExpiryDate($value)
    {
        $this->expiryDate = $value;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * @return Site
     */
    public function getAreaOffice()
    {
        return $this->areaOffice;
    }

    /**
     * @param Site $areaOffice
     */
    public function setAreaOffice($areaOffice)
    {
        $this->areaOffice = $areaOffice;
    }
}
