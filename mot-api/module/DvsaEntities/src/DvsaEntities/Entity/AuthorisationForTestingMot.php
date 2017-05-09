<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * AuthorisationForTestingMot.
 *
 * @ORM\Table(name="auth_for_testing_mot")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\AuthorisationForTestingMotRepository")
 */
class AuthorisationForTestingMot extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="valid_from", type="datetime", nullable=true)
     */
    private $validFrom;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiry_date", type="datetime", nullable=true)
     */
    private $expiryDate;

    /**
     * @var \DvsaEntities\Entity\VehicleClass
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\VehicleClass")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_class_id", referencedColumnName="id")
     * })
     */
    private $vehicleClass;

    /**
     * @var \DvsaEntities\Entity\AuthorisationForTestingMotStatus
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\AuthorisationForTestingMotStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     * })
     */
    private $status;

    /**
     * @var \DvsaEntities\Entity\Person
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Person", inversedBy="authorisationsForTestingMot")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     * })
     */
    private $person;

    /**
     * Set validFrom.
     *
     * @param \DateTime $validFrom
     *
     * @return AuthorisationForTestingMot
     */
    public function setValidFrom($validFrom)
    {
        $this->validFrom = $validFrom;

        return $this;
    }

    /**
     * Get validFrom.
     *
     * @return \DateTime
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * Set expiryDate.
     *
     * @param \DateTime $expiryDate
     *
     * @return AuthorisationForTestingMot
     */
    public function setExpiryDate($expiryDate)
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    /**
     * Get expiryDate.
     *
     * @return \DateTime
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * Set vehicleClass.
     *
     * @param \DvsaEntities\Entity\VehicleClass $vehicleClass
     *
     * @return AuthorisationForTestingMot
     */
    public function setVehicleClass(VehicleClass $vehicleClass = null)
    {
        $this->vehicleClass = $vehicleClass;

        return $this;
    }

    /**
     * Get vehicleClass.
     *
     * @return \DvsaEntities\Entity\VehicleClass
     */
    public function getVehicleClass()
    {
        return $this->vehicleClass;
    }

    /**
     * Set status.
     *
     * @param AuthorisationForTestingMotStatus $status
     *
     * @return $this
     */
    public function setStatus(AuthorisationForTestingMotStatus $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return AuthorisationForTestingMotStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set person.
     *
     * @param \DvsaEntities\Entity\Person $person
     *
     * @return AuthorisationForTestingMot
     */
    public function setPerson(Person $person = null)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * Get person.
     *
     * @return \DvsaEntities\Entity\Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param string $classCode
     *
     * @return bool
     */
    public function isForClass($classCode)
    {
        return $this->getVehicleClass()->getCode() === $classCode;
    }

    /**
     * Checks if a given array of classes (@see Vehicle) are covered by a give
     * set of authorisations.
     *
     * @param \DvsaEntities\Entity\AuthorisationForTestingMot[] $authorisations
     * @param array                                             $classes
     *
     * @return bool
     */
    public static function authorisationForClassesExist($authorisations, $classes)
    {
        $counter = count($classes);
        foreach ($classes as $class) {
            foreach ($authorisations as $auth) {
                if ($auth->isForClass($class)) {
                    --$counter;
                    break;
                }
            }
        }

        return $counter === 0;
    }

    public function isQualified()
    {
        return $this->getStatus()->getCode() === AuthorisationForTestingMotStatusCode::QUALIFIED;
    }
}
