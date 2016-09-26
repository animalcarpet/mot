<?php

namespace DvsaEntities\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DvsaCommon\Constants\PersonContactType;
use DvsaCommon\Enum\PersonAuthType;
use DvsaCommon\Formatting\PersonFullNameFormatter;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Person.
 *
 * @ORM\Table(name="person")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\PersonRepository")
 */
class Person extends Entity
{
    use CommonIdentityTrait;

    const ENTITY_NAME           = 'Person';
    const FIELD_USERNAME_LENGTH = 50;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=50, nullable=false)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="pin", type="string", length=60, nullable=true)
     */
    private $pin;

    /**
     * @var AuthenticationMethod
     *
     * @ORM\OneToOne(targetEntity="AuthenticationMethod", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_auth_type_lookup_id", referencedColumnName="id")
     * })
     */
    private $authenticationMethod;

    /**
     * @var string
     *
     * @ORM\Column(name="user_reference", type="string", length=45, nullable=true)
     */
    private $userReference;

    /**
     * @var Title
     *
     * @ORM\ManyToOne(targetEntity="Title", cascade={"persist", "remove"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="title_id", referencedColumnName="id")
     * })
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=45, nullable=true)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="middle_name", type="string", length=45, nullable=true)
     */
    private $middleName;

    /**
     * @var string
     *
     * @ORM\Column(name="family_name", type="string", length=45, nullable=false)
     */
    private $familyName;

    /**
     * @var Gender
     *
     * @ORM\ManyToOne(targetEntity="Gender", cascade={"persist", "remove"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="gender_id", referencedColumnName="id")
     * })
     */
    private $gender;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_of_birth", type="date", nullable=true)
     */
    private $dateOfBirth;

    /**
     * @var PersonContact[]
     *
     * @ORM\OneToMany(targetEntity="\DvsaEntities\Entity\PersonContact", mappedBy="person", cascade={"persist"})
     */
    private $contacts;

    /**
     * @var integer
     *
     * @ORM\Column(name="otp_failed_attempts", type="integer", length=5, nullable=true)
     */
    private $otpFailedAttempts;

    /**
     * @var boolean
     * @ORM\Column(name="is_account_claim_required", type="boolean", nullable=false)
     */
    private $accountClaimRequired;

    /**
     * @var boolean
     * @ORM\Column(name="is_password_change_required", type="boolean", nullable=false)
     */
    private $passwordChangeRequired;

    /**
     * @var Licence
     *
     * @ORM\OneToOne(targetEntity="Licence", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="driving_licence_id", referencedColumnName="id")
     * })
     */
    private $drivingLicence;

    /**
     * @var AuthorisationForTestingMot[]
     *
     * @ORM\OneToMany(targetEntity="AuthorisationForTestingMot", mappedBy="person", cascade={"persist"})
     */
    private $authorisationsForTestingMot;

    /**
     * @var SiteBusinessRoleMap[]
     *
     * @ORM\OneToMany(targetEntity="DvsaEntities\Entity\SiteBusinessRoleMap", mappedBy="person")
     */
    private $siteBusinessRoleMaps;

    /**
     * @var OrganisationBusinessRoleMap[]
     *
     * @ORM\OneToMany(targetEntity="DvsaEntities\Entity\OrganisationBusinessRoleMap", mappedBy="person")
     */
    private $organisationBusinessRoleMaps;

    /**
     * @var PersonSecurityAnswer[]
     *
     * @ORM\OneToMany(targetEntity="DvsaEntities\Entity\PersonSecurityAnswer", mappedBy="person", cascade={"persist"})
     */
    private $personSecurityAnswers;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="PersonSystemRoleMap", mappedBy="person")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     * })
     */
    private $personSystemRoleMaps;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->contacts = new ArrayCollection();
        $this->authorisationsForTestingMot = new ArrayCollection();
        $this->siteBusinessRoleMaps = new ArrayCollection();
        $this->organisationBusinessRoleMaps = new ArrayCollection();
        $this->personSecurityAnswers = new ArrayCollection();
        $this->setAuthenticationMethod(new AuthenticationMethod(PersonAuthType::PIN));
    }

    /**
     * @param $pin
     * @return Person
     *
     * @return $this
     */
    public function setPin($pin)
    {
        $this->pin = $pin;

        return $this;
    }

    /**
     * @return string
     */
    public function getPin()
    {
        return $this->pin;
    }

    /**
     * @param AuthenticationMethod $method
     *
     * @return Person
     */
    public function setAuthenticationMethod($method)
    {
        $this->authenticationMethod = $method;

        return $this;
    }

    /**
     * @return AuthenticationMethod
     */
    public function getAuthenticationMethod()
    {
        return $this->authenticationMethod;
    }

    /**
     * @param \DateTime $dateOfBirth
     *
     * @return Person
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    /**
     * @param Licence $drivingLicence
     *
     * @return Person
     */
    public function setDrivingLicence($drivingLicence)
    {
        $this->drivingLicence = $drivingLicence;

        return $this;
    }

    /**
     * @param string $familyName
     *
     * @return Person
     */
    public function setFamilyName($familyName)
    {
        $this->familyName = $familyName;

        return $this;
    }

    /**
     * @param OrganisationBusinessRoleMap $organisationBusinessRoleMap
     */
    public function addOrganisationBusinessRoleMap($organisationBusinessRoleMap)
    {
        $this->organisationBusinessRoleMaps->add($organisationBusinessRoleMap);
    }

    /**
     * @param SiteBusinessRoleMap $siteBusinessRoleMaps
     */
    public function addSiteBusinessRoleMaps($siteBusinessRoleMaps)
    {
        $this->siteBusinessRoleMaps->add($siteBusinessRoleMaps);
    }

    /**
     * @param string $firstName
     *
     * @return Person
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @param Gender $gender
     *
     * @return Person
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return PersonContact[]
     */
    public function getContacts()
    {
        return $this->contacts->getIterator();
    }

    /**
     * @param string $middleName
     *
     * @return Person
     */
    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;

        return $this;
    }

    /**
     * @param Title $title
     *
     * @return Person
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param string $userReference
     *
     * @return Person
     */
    public function setUserReference($userReference)
    {
        $this->userReference = $userReference;

        return $this;
    }

    /**
     * @param string $username
     *
     * @return Person
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    public function getDisplayName()
    {
        return (new PersonFullNameFormatter())
            ->format($this->getFirstName(), $this->getMiddleName(), $this->getFamilyName());
    }

    public function getDisplayShortName()
    {
        return self::getShortName($this);
    }

    /**
     * Returns First, Middle and Last name in format F. M. Last_name.
     *
     * @param Person|array $personData
     *
     * @return string
     */
    public static function getShortName($personData)
    {
        if (is_array($personData)) {
            $person = new Person();
            $person
                ->setFirstName(ArrayUtils::tryGet($personData, 'firstName', ''))
                ->setMiddleName(ArrayUtils::tryGet($personData, 'middleName', ''))
                ->setFamilyName(ArrayUtils::tryGet($personData, 'familyName', ''));
        } else {
            $person = $personData;
        }

        return implode(
            '', [
                (strlen($t = trim($person->getFirstName())) ? $t[0] . '. ' : ''),
                (strlen($t = trim($person->getMiddleName())) ? $t[0] . '. ' : ''),
                trim($person->getFamilyName()),
            ]
        );
    }

    /**
     * @return string
     */
    public function getFamilyName()
    {
        return $this->familyName;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * @return Title
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @return Licence
     */
    public function getDrivingLicence()
    {
        return $this->drivingLicence;
    }

    /**
     * @return Gender
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param PersonContact $contact
     */
    public function addContact(PersonContact $contact)
    {
        $this->contacts->add($contact);
    }

    /**
     * @return string
     */
    public function getUserReference()
    {
        return $this->userReference;
    }

    /**
     * @param integer $otpFailedAttempts
     *
     * @return Person
     */
    public function setOtpFailedAttempts($otpFailedAttempts)
    {
        $this->otpFailedAttempts = $otpFailedAttempts;

        return $this;
    }

    /**
     * @return integer
     */
    public function getOtpFailedAttempts()
    {
        return $this->otpFailedAttempts;
    }

    /**
     * @return boolean
     */
    public function isAccountClaimRequired()
    {
        return $this->accountClaimRequired;
    }


    /**
     * @param boolean $accountClaimRequired
     *
     * @return Person
     */
    public function setAccountClaimRequired($accountClaimRequired)
    {
        $this->accountClaimRequired = $accountClaimRequired;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isPasswordChangeRequired()
    {
        return $this->passwordChangeRequired;
    }

    /**
     * @param boolean $passwordChangeRequired
     *
     * @return Person
     */
    public function setPasswordChangeRequired($passwordChangeRequired)
    {
        $this->passwordChangeRequired = $passwordChangeRequired;

        return $this;
    }


    /**
     * Add Vehicle Testing Station.
     *
     * @param \DvsaEntities\Entity\Site $vehicleTestingStation
     *
     * @deprecated This method is only used for unit testing, for convenience.
     *             Please don't use it outside of unit tests.
     *
     * @return Person
     */
    public function addVehicleTestingStation(Site $vehicleTestingStation)
    {
        $position = new SiteBusinessRoleMap();
        $position->setPerson($this);
        $position->setSite($vehicleTestingStation);

        $this->sitePositions[] = $position;

        return $this;
    }

    /**
     * @param string $filterRole SiteBusinessRoleCode
     *                           If specified will only take those sites where a person fulfills the given role.
     *                           If not, then all sites where the person works will be returned.
     *
     * @return Site[]
     */
    public function findSites($filterRole = null)
    {
        $positions = $this->findActiveSitePositions($filterRole);

        $sites = ArrayUtils::map(
            $positions,
            function (SiteBusinessRoleMap $position) {
                return $position->getSite();
            }
        );

        // One person can have multiple roles in a site, so we want to remove recurring sites.
        $sites = array_unique($sites, SORT_REGULAR);

        return $sites;
    }

    /**
     * Gets all positions this person fulfills in all sites.
     * The collection contains also positions that are pending, rejected etc.
     *
     * @param string $filterRole SiteBusinessRoleCode Get only those positions that relate to to given role.
     *
     * @return SiteBusinessRoleMap[]
     */
    public function findAllSitePositions($filterRole = null)
    {
        $sitePositions = $this->siteBusinessRoleMaps->toArray();

        if ($filterRole) {
            $sitePositions = ArrayUtils::filter(
                $sitePositions,
                function (SiteBusinessRoleMap $position) use ($filterRole) {
                    //TODO VM-8254 11: replace with getCode()
                    return $position->getSiteBusinessRole()->getName() === $filterRole;
                }
            );
        }

        return $sitePositions;
    }

    /**
     * Gets all ACTIVE positions the person fulfills in all sites.
     * Does not include pending positions, rejected ones etc.
     *
     * @param string $filterRole SiteBusinessRoleCode Get only those positions that relate to the given role.
     *
     * @return SiteBusinessRoleMap[]
     */
    public function findActiveSitePositions($filterRole = null)
    {
        return ArrayUtils::filter(
            $this->findAllSitePositions($filterRole),
            function (SiteBusinessRoleMap $position) {
                return $position->getBusinessRoleStatus()->getCode() === BusinessRoleStatusCode::ACTIVE;
            }
        );
    }

    /**
     * @param string $filterRole OrganisationBusinessRoleCode
     *                           If specified will only take those organisations where a person fulfills the given role.
     *                           If not, then all organisations where the person works will be returned.
     *
     * @return Organisation[]
     */
    public function findOrganisations($filterRole = null)
    {
        $positions = $this->findActiveOrganisationPositions($filterRole);

        $organisation = ArrayUtils::map(
            $positions,
            function (OrganisationBusinessRoleMap $position) {
                return $position->getOrganisation();
            }
        );

        // One person can have multiple roles in an organisation, so we want to remove recurring sites.
        $organisation = array_unique($organisation, SORT_REGULAR);

        return $organisation;
    }

    /**
     * @param string $filterRole OrganisationBusinessRoleCode
     *                           If specified will only take those authorised examiners where a person fulfills
     *                           the given role.
     *                           If not, then all authorised examiners where the person works will be returned.
     *
     * @return Organisation[]
     */
    public function findAuthorisedExaminers($filterRole = null)
    {
        $allOrganisations = $this->findOrganisations($filterRole);

        $authorisedExaminers = ArrayUtils::filter(
            $allOrganisations,
            function (Organisation $organisation) {
                return $organisation->isAuthorisedExaminer();
            }
        );

        return $authorisedExaminers;
    }

    /**
     * Gets all positions this person fulfills in all organisations.
     * The collection contains also positions that are pending, rejected etc.
     *
     * @param string $filterRole OrganisationBusinessRoleCode Get only those positions that relate to to given role.
     *
     * @return OrganisationBusinessRoleMap[]
     */
    public function findAllOrganisationPositions($filterRole = null)
    {
        $organisationPositions = $this->organisationBusinessRoleMaps->toArray();

        if ($filterRole) {
            $organisationPositions = ArrayUtils::filter(
                $organisationPositions,
                function (OrganisationBusinessRoleMap $position) use ($filterRole) {
                    //TODO VM-8254 8: replace with getCode()
                    return $position->getOrganisationBusinessRole()->getName() === $filterRole;
                }
            );
        }

        return $organisationPositions;
    }

    /**
     * Gets all ACTIVE positions the person fulfills in all organisations.
     * Does not include pending positions, rejected ones etc.
     *
     * @param string $filterRole OrganisationBusinessRoleCode Get only those positions that relate to the given role.
     *
     * @return OrganisationBusinessRoleMap[]
     */
    public function findActiveOrganisationPositions($filterRole = null)
    {
        $positions = $this->findAllOrganisationPositions($filterRole);

        return ArrayUtils::filter(
            $positions,
            function (OrganisationBusinessRoleMap $position) {
                return $position->getBusinessRoleStatus()->getCode() === BusinessRoleStatusCode::ACTIVE;
            }
        );
    }

    /**
     * @return AuthorisationForTestingMot[]
     */
    public function getAuthorisationsForTestingMot()
    {
        return $this->authorisationsForTestingMot;
    }

    public function addAuthorisationForTestingMot(AuthorisationForTestingMot $auth)
    {
        $this->authorisationsForTestingMot[] = $auth;
    }

    /**
     * @param array $authorisationsForTestingMot
     *
     * @return Person
     */
    public function setAuthorisationsForTestingMot(array $authorisationsForTestingMot)
    {
        $this->authorisationsForTestingMot = $authorisationsForTestingMot;

        return $this;
    }

    /**
     * Please note this function is used to make Person backward compatible
     * with a functionality relying on tester table. Each scenario should
     * be considered carefully in terms what authorisations are needed
     * for which classes.
     *
     * Checks if a person is authorised to test
     *
     * @return bool
     */
    public function isTester()
    {
        return ArrayUtils::anyMatch(
            $this->authorisationsForTestingMot,
            function (AuthorisationForTestingMot $authorisation) {
                return in_array(
                    $authorisation->getStatus()->getCode(),
                    [
                        AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED,
                        AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED,
                        AuthorisationForTestingMotStatusCode::REFRESHER_NEEDED,
                        AuthorisationForTestingMotStatusCode::QUALIFIED,
                        AuthorisationForTestingMotStatusCode::SUSPENDED,
                    ]
                );
            }
        );
    }

    /**
     * Checks if a person is qualified to test.
     *
     * @return bool
     */
    public function isQualifiedTester()
    {
        return ArrayUtils::anyMatch(
            $this->authorisationsForTestingMot,
            function (AuthorisationForTestingMot $authorisation) {
                return $authorisation->isQualified();
            }
        );
    }

    /**
     * Checks if a person is qualified to test.
     *
     * @param VehicleClass $vehicleClass
     *
     * @return bool
     */
    public function isQualifiedTesterForVehicleClass(VehicleClass $vehicleClass)
    {
        return ArrayUtils::anyMatch(
            $this->authorisationsForTestingMot,
            function (AuthorisationForTestingMot $authorisation) use ($vehicleClass) {
                return $authorisation->isQualified() && $authorisation->isForClass($vehicleClass->getCode());
            }
        );
    }

    public function addSecurityAnswer(PersonSecurityAnswer $securityQuestion)
    {
        $this->personSecurityAnswers[] = $securityQuestion;

        return $this;
    }

    public function replaceSecurityAnswers(array $newSecurityAnswers)
    {
        $this->personSecurityAnswers->clear();

        foreach ($newSecurityAnswers as $newSecurityAnswer) {
            $this->addSecurityAnswer($newSecurityAnswer);
        }

        return $this;
    }

    public function getSecurityAnswers()
    {
        return $this->personSecurityAnswers;
    }

    /**
     * This function return the first primary email for a person
     *
     * @return null|string
     */
    public function getPrimaryEmail()
    {
        foreach ($this->contacts as $contactDetails) {
            if ($contactDetails->getDetails()->getPrimaryEmail() != null
                && empty($contactDetails->getDetails()->getPrimaryEmail()->getEmail()) === false) {
                return $contactDetails->getDetails()->getPrimaryEmail()->getEmail();
            }
        }
        return null;

    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPersonSystemRoleMaps()
    {
        return $this->personSystemRoleMaps;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $personSystemRoleMaps
     * @return $this
     */
    public function setPersonSystemRoleMaps($personSystemRoleMaps)
    {
        $this->personSystemRoleMaps = $personSystemRoleMaps;
        return $this;
    }
}
