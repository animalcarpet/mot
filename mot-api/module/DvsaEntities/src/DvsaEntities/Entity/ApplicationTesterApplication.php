<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\UUIDIdentityTrait;

/**
 * ApplicationTesterApplication.
 *
 * @ORM\Table(name="application_tester_application", indexes={@ORM\Index(name="fk_tester_application_status", columns={"application_status"}), @ORM\Index(name="fk_tester", columns={"application_tester_id"}), @ORM\Index(name="fk_user", columns={"person_id"})})
 * @ORM\Entity
 */
class ApplicationTesterApplication
{
    use UUIDIdentityTrait;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date_time", type="datetime", nullable=true)
     */
    private $startDateTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="submit_date_time", type="datetime", nullable=true)
     */
    private $submitDateTime;

    /**
     * @var string
     *
     * @ORM\Column(name="vehicle_test_class_section_state", type="string", length=50, nullable=false)
     */
    private $vehicleTestClassSectionState = '';

    /**
     * @var string
     *
     * @ORM\Column(name="experience_section_state", type="string", length=50, nullable=false)
     */
    private $experienceSectionState = '';

    /**
     * @var string
     *
     * @ORM\Column(name="qualifications_section_state", type="string", length=50, nullable=false)
     */
    private $qualificationsSectionState = '';

    /**
     * @var string
     *
     * @ORM\Column(name="convictions_section_state", type="string", length=50, nullable=false)
     */
    private $convictionsSectionState = '';

    /**
     * @var \DvsaEntities\Entity\ApplicationTester
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\ApplicationTester")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="application_tester_id", referencedColumnName="id")
     * })
     */
    private $applicationTester;

    /**
     * @var \DvsaEntities\Entity\ApplicationStatus
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\ApplicationStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="application_status", referencedColumnName="application_status")
     * })
     */
    private $applicationStatus;

    /**
     * @var \DvsaEntities\Entity\Person
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Person")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     * })
     */
    private $person;

    /**
     * Set startDateTime.
     *
     * @param \DateTime $startDateTime
     *
     * @return ApplicationTesterApplication
     */
    public function setStartDateTime($startDateTime)
    {
        $this->startDateTime = $startDateTime;

        return $this;
    }

    /**
     * Get startDateTime.
     *
     * @return \DateTime
     */
    public function getStartDateTime()
    {
        return $this->startDateTime;
    }

    /**
     * Set submitDateTime.
     *
     * @param \DateTime $submitDateTime
     *
     * @return ApplicationTesterApplication
     */
    public function setSubmitDateTime($submitDateTime)
    {
        $this->submitDateTime = $submitDateTime;

        return $this;
    }

    /**
     * Get submitDateTime.
     *
     * @return \DateTime
     */
    public function getSubmitDateTime()
    {
        return $this->submitDateTime;
    }

    /**
     * Set vehicleTestClassSectionState.
     *
     * @param string $vehicleTestClassSectionState
     *
     * @return ApplicationTesterApplication
     */
    public function setVehicleTestClassSectionState($vehicleTestClassSectionState)
    {
        $this->vehicleTestClassSectionState = $vehicleTestClassSectionState;

        return $this;
    }

    /**
     * Get vehicleTestClassSectionState.
     *
     * @return string
     */
    public function getVehicleTestClassSectionState()
    {
        return $this->vehicleTestClassSectionState;
    }

    /**
     * Set experienceSectionState.
     *
     * @param string $experienceSectionState
     *
     * @return ApplicationTesterApplication
     */
    public function setExperienceSectionState($experienceSectionState)
    {
        $this->experienceSectionState = $experienceSectionState;

        return $this;
    }

    /**
     * Get experienceSectionState.
     *
     * @return string
     */
    public function getExperienceSectionState()
    {
        return $this->experienceSectionState;
    }

    /**
     * Set qualificationsSectionState.
     *
     * @param string $qualificationsSectionState
     *
     * @return ApplicationTesterApplication
     */
    public function setQualificationsSectionState($qualificationsSectionState)
    {
        $this->qualificationsSectionState = $qualificationsSectionState;

        return $this;
    }

    /**
     * Get qualificationsSectionState.
     *
     * @return string
     */
    public function getQualificationsSectionState()
    {
        return $this->qualificationsSectionState;
    }

    /**
     * Set convictionsSectionState.
     *
     * @param string $convictionsSectionState
     *
     * @return ApplicationTesterApplication
     */
    public function setConvictionsSectionState($convictionsSectionState)
    {
        $this->convictionsSectionState = $convictionsSectionState;

        return $this;
    }

    /**
     * Get convictionsSectionState.
     *
     * @return string
     */
    public function getConvictionsSectionState()
    {
        return $this->convictionsSectionState;
    }

    /**
     * Set applicationTester.
     *
     * @param \DvsaEntities\Entity\ApplicationTester $applicationTester
     *
     * @return ApplicationTesterApplication
     */
    public function setApplicationTester(\DvsaEntities\Entity\ApplicationTester $applicationTester = null)
    {
        $this->applicationTester = $applicationTester;

        return $this;
    }

    /**
     * Get applicationTester.
     *
     * @return \DvsaEntities\Entity\ApplicationTester
     */
    public function getApplicationTester()
    {
        return $this->applicationTester;
    }

    /**
     * Set applicationStatus.
     *
     * @param \DvsaEntities\Entity\ApplicationStatus $applicationStatus
     *
     * @return ApplicationTesterApplication
     */
    public function setApplicationStatus(\DvsaEntities\Entity\ApplicationStatus $applicationStatus = null)
    {
        $this->applicationStatus = $applicationStatus;

        return $this;
    }

    /**
     * Get applicationStatus.
     *
     * @return \DvsaEntities\Entity\ApplicationStatus
     */
    public function getApplicationStatus()
    {
        return $this->applicationStatus;
    }

    /**
     * Set person.
     *
     * @param \DvsaEntities\Entity\Person $person
     *
     * @return ApplicationTesterApplication
     */
    public function setPerson(\DvsaEntities\Entity\Person $person = null)
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
}
