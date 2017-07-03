<?php

namespace DvsaEntities\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DvsaCommon\Enum\AuthorisationForTestingMotAtSiteStatusCode;
use DvsaCommon\Enum\OrganisationSiteStatusCode;
use DvsaCommon\Enum\SiteTypeCode;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Site.
 *
 * @ORM\Table(name="site",
 *  options={
 *      "collate"="utf8_general_ci",
 *      "charset"="utf8",
 *      "engine"="InnoDB"},
 *  uniqueConstraints={@ORM\UniqueConstraint(name="site_number", columns={"site_number"}),
 * @ORM\UniqueConstraint(name="id_UNIQUE",
 *  columns={"id"})},
 *  indexes={
 * @ORM\Index(name="fk_site_3_idx", columns={"organisation_id"}),
 * @ORM\Index(name="fk_site_4_idx", columns={"created_by"}),
 * @ORM\Index(name="fk_site_5_idx", columns={"last_updated_by"}),
 * @ORM\Index(name="fk_site_type_id", columns={"type_id"}),
 * @ORM\Index(name="fk_site_assessment_id",
 *      columns={"last_site_assessment_id"})})
 *
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\SiteRepository")
 */
class Site extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name = '';

    /**
     * @var SiteContact[]
     *
     * @ORM\OneToMany(
     *  targetEntity="SiteContact", mappedBy="site", fetch="LAZY", cascade={"persist"}, orphanRemoval=true
     * )
     */
    private $contacts;

    /**
     * @var AuthorisationForTestingMotAtSite[]
     *
     * @ORM\OneToMany(
     *      targetEntity="AuthorisationForTestingMotAtSite",
     *      fetch="EAGER",
     *      mappedBy="site"
     * )
     */
    private $authorisationsForTestingMotAtSite;

    /**
     * @var string
     *
     * @ORM\Column(name="site_number", type="string", length=8, nullable=true)
     */
    private $siteNumber;

    /**
     * @var \DvsaEntities\Entity\BrakeTestType
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\BrakeTestType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="default_brake_test_class_1_and_2_id", referencedColumnName="id")
     * })
     */
    private $defaultBrakeTestClass1And2;

    /**
     * @var \DvsaEntities\Entity\BrakeTestType
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\BrakeTestType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="default_service_brake_test_class_3_and_above_id", referencedColumnName="id")
     * })
     */
    private $defaultServiceBrakeTestClass3AndAbove;

    /**
     * @var \DvsaEntities\Entity\BrakeTestType
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\BrakeTestType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="default_parking_brake_test_class_3_and_above_id", referencedColumnName="id")
     * })
     */
    private $defaultParkingBrakeTestClass3AndAbove;

    /**
     * @var EnforcementSiteAssessment
     *
     * @ORM\ManyToOne(targetEntity="EnforcementSiteAssessment", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="last_site_assessment_id", referencedColumnName="id")
     * })
     */
    private $lastSiteAssessment;

    /**
     * @var Organisation
     *
     * @ORM\ManyToOne(targetEntity="Organisation", fetch="EAGER", inversedBy="sites")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="organisation_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $organisation;

    /**
     * @var bool
     *
     * @ORM\Column(name="dual_language", type="boolean", nullable=false)
     */
    private $dualLanguage = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="scottish_bank_holiday", type="boolean", nullable=false)
     */
    private $scottishBankHoliday = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="decimal", precision=8, scale=5, nullable=true)
     */
    private $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="decimal", precision=8, scale=5, nullable=true)
     */
    private $longitude;

    /**
     * @var SiteBusinessRoleMap[]
     *                            The personnel that works
     *
     * @ORM\OneToMany(targetEntity="SiteBusinessRoleMap", mappedBy="site", fetch="LAZY")
     */
    private $positions;

    /**
     * @var SiteFacility[]
     *
     * @ORM\OneToMany(
     *  targetEntity="SiteFacility",
     *  mappedBy="vehicleTestingStation",
     *  fetch="LAZY"
     * )
     */
    private $facilities;

    /**
     * @var Equipment[]
     *
     * @ORM\OneToMany(targetEntity="DvsaEntities\Entity\Equipment", mappedBy="site", fetch="LAZY")
     */
    private $equipments;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="SiteComment", mappedBy="site")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     * })
     */
    private $siteComments;

    /**
     * @var MotTest
     *
     * @ORM\OneToMany(targetEntity="MotTest", mappedBy="vehicleTestingStation", fetch="LAZY")
     */
    private $tests;

    /**
     * @var \DvsaEntities\Entity\SiteType
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\SiteType", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     * })
     */
    private $type;

    /**
     * @var SiteTestingDailySchedule[]
     *
     * @ORM\OneToMany(targetEntity="DvsaEntities\Entity\SiteTestingDailySchedule", mappedBy="site", fetch="LAZY")
     */
    private $siteTestingSchedule;

    /**
     * @ORM\ManyToOne(targetEntity="NonWorkingDayCountry")
     * @ORM\JoinColumn(name="non_working_day_country_lookup_id", referencedColumnName="id")
     **/
    private $nonWorkingDayCountry;

    /**
     * @var OrganisationSiteMap[]
     *
     * @ORM\OneToMany(targetEntity="OrganisationSiteMap", mappedBy="site", fetch="LAZY")
     */
    private $associationsWithAe;

    /**
     * @ORM\ManyToOne(targetEntity="SiteStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="site_status_id", referencedColumnName="id")
     * })
     */
    private $status;

    /**
     * @var SiteRiskAssessment
     *
     * @ORM\OneToMany(targetEntity="EnforcementSiteAssessment", fetch="LAZY", mappedBy="site")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     * })
     */
    private $siteRiskAssessments;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="status_changed_on", type="datetime", nullable=true)
     */
    private $statusChangedOn;

    /**
     * @return \DvsaEntities\Entity\SiteTestingDailySchedule[]
     */
    public function getSiteTestingSchedule()
    {
        return $this->siteTestingSchedule;
    }

    public function setSiteTestingSchedule(array $schedule)
    {
        $this->siteTestingSchedule = $schedule;

        return $this;
    }

    public function setTests($tests)
    {
        $this->tests = $tests;

        return $this;
    }

    /**
     * @param Organisation $organisation
     *
     * @return Site
     * @codeCoverageIgnore
     */
    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * @return Organisation
     * @codeCoverageIgnore
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->positions = new ArrayCollection();
        $this->siteComments = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->authorisationsForTestingMotAtSite = new ArrayCollection();
        $this->associationsWithAe = new ArrayCollection();
        $this->siteRiskAssessments = new ArrayCollection();
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSiteComments()
    {
        return $this->siteComments;
    }

    /**
     * @param SiteComment $siteComment
     *
     * @return Site
     */
    public function addSiteComment(SiteComment $siteComment)
    {
        $this->siteComments[] = $siteComment;

        return $this;
    }

    /**
     * Get the associated Authorised Examiner.
     *
     * @return AuthorisationForAuthorisedExaminer|null
     */
    public function getAuthorisedExaminer()
    {
        $authorisedExaminer = null;

        if ($this->organisation) {
            $authorisedExaminer = $this->organisation->getAuthorisedExaminer();
        }

        return $authorisedExaminer;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Site
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        if ($this->isOffsite()) {
            $siteComments = $this->getSiteComments();
            if (count($siteComments) > 0) {
                $commentText = trim($siteComments->first()->getComment()->getComment());
                if (!empty($commentText)) {
                    return $commentText;
                }
            }
        }

        return $this->name;
    }

    /**
     * Get address.
     *
     * @return Address
     */
    public function getAddress()
    {
        $businessContact = $this->getBusinessContact();

        return $businessContact !== null ? $businessContact->getDetails()->getAddress() : null;
    }

    /**
     * @return SiteContact[]
     */
    public function getContacts()
    {
        return $this->contacts->getIterator();
    }

    public function getBusinessContact()
    {
        return $this->getContactByType(SiteContactTypeCode::BUSINESS);
    }

    public function getCorrespondenceContact()
    {
        return $this->getContactByType(SiteContactTypeCode::CORRESPONDENCE);
    }

    /**
     * @param string $typeCode
     *
     * @return SiteContact|null
     */
    public function getContactByType($typeCode)
    {
        return ArrayUtils::firstOrNull(
            $this->getContacts(),
            function (SiteContact $contact) use ($typeCode) {
                return $contact->getType()->getCode() === $typeCode;
            }
        );
    }

    public function setContact(ContactDetail $contactDetail, SiteContactType $type)
    {
        /** @var SiteContact $oldContact */
        $oldContact = $this->getContactByType($type->getCode());

        $this->contacts->removeElement($oldContact);

        $contact = new SiteContact($contactDetail, $type, $this);
        $this->contacts->add($contact);

        return $this;
    }

    /**
     * Set site number - the existing VTS "ID".
     *
     * @param string $siteNumber
     *
     * @return Site
     */
    public function setSiteNumber($siteNumber)
    {
        $this->siteNumber = $siteNumber;

        return $this;
    }

    /**
     * Get siteNumber.
     *
     * @return string
     */
    public function getSiteNumber()
    {
        if ($this->siteNumber === null) {
            return '';
        }

        return $this->siteNumber;
    }

    /**
     * @param BrakeTestType $defaultBrakeTestClass1And2
     *
     * @return Site
     */
    public function setDefaultBrakeTestClass1And2($defaultBrakeTestClass1And2)
    {
        $this->defaultBrakeTestClass1And2 = $defaultBrakeTestClass1And2;

        return $this;
    }

    /**
     * @return BrakeTestType
     */
    public function getDefaultBrakeTestClass1And2()
    {
        return $this->defaultBrakeTestClass1And2;
    }

    /**
     * @param BrakeTestType $defaultParkingBrakeTestClass3AndAbove
     *
     * @return Site
     */
    public function setDefaultParkingBrakeTestClass3AndAbove($defaultParkingBrakeTestClass3AndAbove)
    {
        $this->defaultParkingBrakeTestClass3AndAbove = $defaultParkingBrakeTestClass3AndAbove;

        return $this;
    }

    /**
     * @return BrakeTestType
     */
    public function getDefaultParkingBrakeTestClass3AndAbove()
    {
        return $this->defaultParkingBrakeTestClass3AndAbove;
    }

    /**
     * @param BrakeTestType $defaultServiceBrakeTestClass3AndAbove
     *
     * @return Site
     */
    public function setDefaultServiceBrakeTestClass3AndAbove($defaultServiceBrakeTestClass3AndAbove)
    {
        $this->defaultServiceBrakeTestClass3AndAbove = $defaultServiceBrakeTestClass3AndAbove;

        return $this;
    }

    /**
     * @return BrakeTestType
     */
    public function getDefaultServiceBrakeTestClass3AndAbove()
    {
        return $this->defaultServiceBrakeTestClass3AndAbove;
    }

    /**
     * Get authorisationsForTestingMotAtSite.
     *
     * @return AuthorisationForTestingMotAtSite[]
     */
    public function getAuthorisationForTestingMotAtSite()
    {
        return $this->authorisationsForTestingMotAtSite;
    }

    /**
     * @param AuthorisationForTestingMotAtSite $authorisationsForTestingMotAtSite
     *
     * @return Site
     */
    public function addAuthorisationsForTestingMotAtSite(
        AuthorisationForTestingMotAtSite $authorisationsForTestingMotAtSite
    ) {
        $this->authorisationsForTestingMotAtSite[] = $authorisationsForTestingMotAtSite;

        return $this;
    }

    /**
     * Checks if Site has authorisation to test a particular vehicle class.
     *
     * @param VehicleClass $vehicleClass
     *
     * @return bool
     */
    public function hasAuthForVehicleClass(VehicleClass $vehicleClass)
    {
        /** @var AuthorisationForTestingMotAtSite $authorisationForTestingMotAtSite */
        foreach ($this->authorisationsForTestingMotAtSite as $authorisationForTestingMotAtSite) {
            if ($authorisationForTestingMotAtSite->getVehicleClass()->getCode() === $vehicleClass->getCode()
                && $authorisationForTestingMotAtSite->isApproved()
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set the associated Site Assessment.
     *
     * @param EnforcementSiteAssessment $siteAssessment
     *
     * @return Site
     */
    public function setLastSiteAssessment($siteAssessment)
    {
        $this->lastSiteAssessment = $siteAssessment;

        return $this;
    }

    /**
     * Get the associated Site Assessment.
     *
     * @return EnforcementSiteAssessment
     */
    public function getLastSiteAssessment()
    {
        return $this->lastSiteAssessment;
    }

    public function isVehicleTestingStation()
    {
        return true;
    }

    public function getPositions()
    {
        return $this->positions;
    }

    public function setPositions($positions)
    {
        $this->positions = $positions;

        return $this;
    }

    /**
     * @param SiteFacility[] $facilities
     *
     * @return Site
     */
    public function setFacilities($facilities)
    {
        $this->facilities = $facilities;

        return $this;
    }

    /**
     * @return SiteFacility[]
     */
    public function getFacilities()
    {
        return $this->facilities;
    }

    /**
     * Set dualLanguage.
     *
     * @param bool $dualLanguage
     *
     * @return Site
     */
    public function setDualLanguage($dualLanguage)
    {
        $this->dualLanguage = $dualLanguage;

        return $this;
    }

    /**
     * Get dualLanguage.
     *
     * @return bool
     */
    public function getDualLanguage()
    {
        return $this->dualLanguage;
    }

    /**
     * Set scottishBankHoliday.
     *
     * @param bool $scottishBankHoliday
     *
     * @return Site
     */
    public function setScottishBankHoliday($scottishBankHoliday)
    {
        $this->scottishBankHoliday = $scottishBankHoliday;

        return $this;
    }

    /**
     * Get scottishBankHoliday.
     *
     * @return bool
     */
    public function getScottishBankHoliday()
    {
        return $this->scottishBankHoliday;
    }

    /**
     * Set latitude.
     *
     * @param string $latitude
     *
     * @return Site
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude.
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude.
     *
     * @param string $longitude
     *
     * @return Site
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude.
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set type.
     *
     * @param SiteType $type
     *
     * @return Site
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return \DvsaEntities\Entity\SiteType
     */
    public function getType()
    {
        return $this->type;
    }

    public function getEquipments()
    {
        return $this->equipments;
    }

    /**
     * Create a random value that will satisfy the unique constraint but also makes
     * it easy to find them all later.
     *
     * TODO: Change this to a relevant business-centric value that makes sense!
     *
     * @return string the reinspection number that was created
     */
    public function setReinspectionSiteNumber()
    {
        $this->setSiteNumber(
            substr(uniqid(), -8) // last 8 is microtime accuracy!
        );
    }

    /**
     * @return VehicleClass[]
     */
    public function getApprovedVehicleClasses()
    {
        return $this->vehicleClassesOfType(AuthorisationForTestingMotAtSiteStatusCode::APPROVED);
    }

    /**
     * @param $authId
     *
     * @return array
     */
    public function getApprovedAuthorisationForTestingMotAtSite()
    {
        return $this->getAuthorisationForTestingMotAtSiteOfType(AuthorisationForTestingMotAtSiteStatusCode::APPROVED);
    }

    /**
     * @param $authId
     *
     * @return AuthorisationForTestingMotAtSite[]
     */
    private function getAuthorisationForTestingMotAtSiteOfType($authId)
    {
        $qualifiedAuthorisationForTestingMotAtSite = ArrayUtils::filter(
            $this->authorisationsForTestingMotAtSite,
            function (AuthorisationForTestingMotAtSite $authorisationForTestingMotAtSite) use ($authId) {
                return $authorisationForTestingMotAtSite->getStatus()->getCode() === $authId;
            }
        );

        return $qualifiedAuthorisationForTestingMotAtSite;
    }

    /**
     * @return array
     */
    private function vehicleClassesOfType($authId)
    {
        $qualifiedAuthorisationForTestingMotAtSite = $this->getAuthorisationForTestingMotAtSiteOfType($authId);

        return ArrayUtils::map(
            $qualifiedAuthorisationForTestingMotAtSite,
            function (AuthorisationForTestingMotAtSite $authorisationForTestingMotAtSite) {
                return $authorisationForTestingMotAtSite->getVehicleClass();
            }
        );
    }

    public function isOffsite()
    {
        return ($this->getType() instanceof SiteType) && ($this->getType()->getCode() === SiteTypeCode::OFFSITE);
    }

    /**
     * @return NonWorkingDayCountry
     */
    public function getNonWorkingDayCountry()
    {
        return $this->nonWorkingDayCountry;
    }

    /**
     * @param NonWorkingDayCountry $nonWorkingDayCountry
     *
     * @return $this
     */
    public function setNonWorkingDayCountry(NonWorkingDayCountry $nonWorkingDayCountry = null)
    {
        $this->nonWorkingDayCountry = $nonWorkingDayCountry;

        return $this;
    }

    /**
     * @return ArrayCollection|OrganisationSiteMap[]
     */
    public function getAssociationWithAe()
    {
        return $this->associationsWithAe;
    }

    public function getActiveAssociationWithAe()
    {
        $maps = ArrayUtils::filter(
            $this->getAssociationWithAe(),
            function (OrganisationSiteMap $map) {
                return $map->getStatus()->getCode() === OrganisationSiteStatusCode::ACTIVE;
            }
        );

        return current($maps);
    }

    /**
     * @param SiteStatus $status
     *
     * @return $this
     */
    public function setStatus(SiteStatus $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return SiteStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param AuthorisationForTestingMotAtSite $toRemove
     */
    public function removeAuthorisationForTestingMotAtSite(AuthorisationForTestingMotAtSite $toRemove)
    {
        $this->authorisationsForTestingMotAtSite->removeElement($toRemove);
    }

    /**
     * Set statusChangedOn.
     *
     * @param \DateTime $statusChangedOn
     *
     * @return Site
     */
    public function setStatusChangedOn(\DateTime $statusChangedOn)
    {
        $this->statusChangedOn = $statusChangedOn;

        return $this;
    }

    /**
     * Get statusChangedOn.
     *
     * @return \DateTime
     */
    public function getStatusChangedOn()
    {
        return $this->statusChangedOn;
    }

    /**
     * @return ArrayCollection|SiteRiskAssessment
     */
    public function getRiskAssessments()
    {
        return $this->siteRiskAssessments;
    }

    /**
     * @param EnforcementSiteAssessment $riskAssessment
     */
    public function addRiskAssessment(EnforcementSiteAssessment $riskAssessment)
    {
        $this->siteRiskAssessments->add($riskAssessment);
    }
}
