<?php

namespace Dashboard\Model;

use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Constants\Role;

/**
 * Data model for personal details.
 */
class PersonalDetails
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $middleName;

    /**
     * @var string
     */
    private $surname;

    private $title;

    /**
     * @var string
     */
    private $gender;

    /**
     * @var string
     */
    private $username;

    private $positions;

    /**
     * @var string
     */
    private $dateOfBirth;

    /**
     * @var string
     */
    private $addressLine1;

    /**
     * @var string
     */
    private $addressLine2;

    /**
     * @var string
     */
    private $addressLine3;

    /**
     * @var string
     */
    private $town;

    /**
     * @var string
     */
    private $postcode;

    /**
     * @var null|string
     */
    private $email;

    /**
     * @var null|string
     */
    private $emailConfirmation;

    /**
     * @var null|string
     */
    private $phoneNumber;

    /**
     * @var string
     */
    private $drivingLicenceNumber;

    /**
     * @var string
     */
    private $drivingLicenceRegion;

    /**
     * @var array
     */
    private $displayableRoles = [];

    /**
     * @var array
     */
    private $rolesAndAssociations = [];

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this
                ->setId(ArrayUtils::get($data, 'id'))
                ->setFirstName(ArrayUtils::get($data, 'firstName'))
                ->setMiddleName(ArrayUtils::get($data, 'middleName'))
                ->setSurname(ArrayUtils::get($data, 'surname'))
                ->setDateOfBirth(ArrayUtils::get($data, 'dateOfBirth'))
                ->setTitle(ArrayUtils::get($data, 'title'))
                ->setGender(ArrayUtils::get($data, 'gender'))
                ->setAddressLine1(ArrayUtils::get($data, 'addressLine1'))
                ->setAddressLine2(ArrayUtils::get($data, 'addressLine2'))
                ->setAddressLine3(ArrayUtils::get($data, 'addressLine3'))
                ->setTown(ArrayUtils::get($data, 'town'))
                ->setPostcode(ArrayUtils::get($data, 'postcode'))
                ->setEmail(ArrayUtils::get($data, 'email'))
                ->setEmailConfirmation(ArrayUtils::tryGet($data, 'emailConfirmation', ArrayUtils::get($data, 'email')))
                ->setPhoneNumber(ArrayUtils::get($data, 'phone'))
                ->setDrivingLicenceNumber(ArrayUtils::get($data, 'drivingLicenceNumber'))
                ->setDrivingLicenceRegion(ArrayUtils::get($data, 'drivingLicenceRegion'))
                ->setUsername(ArrayUtils::get($data, 'username'))
                ->setPositions(ArrayUtils::get($data, 'positions'));

            $this->rolesAndAssociations = ArrayUtils::get($data, 'roles');

            $this->displayableRoles = $this->extractDisplayableRoleNames();
        }
    }

    /**
     * @param mixed $id
     *
     * @return PersonalDetails
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getDisplayableRoles()
    {
        return $this->displayableRoles;
    }

    /**
     * @return array
     */
    public function getRolesAndAssociations()
    {
        return $this->rolesAndAssociations;
    }

    /**
     * Returns the persons assigned system roles. USER and CRON are filtered
     * from roles as we don't want to display these in the user profile.
     *
     * @return array
     */
    public function getDisplayableSystemRoles()
    {
        $roles = $this->getRolesAndAssociations();
        $rolesFiltered = [];

        foreach ($roles['system']['roles'] as $role) {
            if ($role != Role::CRON && $role != Role::USER && $role != Role::TESTER_ACTIVE) {
                $rolesFiltered[] = $role;
            }
        }

        return $rolesFiltered;
    }

    public function getSystemRoles()
    {
        $roles = $this->getRolesAndAssociations();

        return $roles['system']['roles'];
    }

    public function getSitesRolesAndAssociations()
    {
        $roles = $this->getRolesAndAssociations();

        return $roles['sites'];
    }

    /**
     * Returns an array of all site and organisation roles,.
     *
     * @return array
     */
    public function getSiteAndOrganisationRoles()
    {
        $roles = $this->getRolesAndAssociations();
        unset($roles['system']);

        return $roles;
    }

    /**
     * @param $positions
     *
     * @return PersonalDetails
     */
    public function setPositions($positions)
    {
        $this->positions = $positions;

        return $this;
    }

    public function getPositions()
    {
        return $this->positions;
    }

    /**
     * @param mixed $gender
     *
     * @return PersonalDetails
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param mixed $firstName
     *
     * @return PersonalDetails
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $middleName
     *
     * @return PersonalDetails
     */
    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * @param mixed $surname
     *
     * @return PersonalDetails
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Returns all parts of the users name, separated by a single space character
     * e.g. Title First Middle Last. If any part is empty, it will be removed before the
     * other parts are joined.
     *
     * @return string
     */
    public function getFullName()
    {
        return implode(
            ' ', array_filter(
                [
                    $this->getTitle(),
                    $this->getFirstName(),
                    $this->getMiddleName(),
                    $this->getSurname(),
                ],
                'strlen'
            )
        );
    }

    /**
     * @param $username
     *
     * @return PersonalDetails
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $title
     *
     * @return PersonalDetails
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $addressLine1
     *
     * @return PersonalDetails
     */
    public function setAddressLine1($addressLine1)
    {
        $this->addressLine1 = $addressLine1;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    /**
     * @param mixed $addressLine2
     *
     * @return PersonalDetails
     */
    public function setAddressLine2($addressLine2)
    {
        $this->addressLine2 = $addressLine2;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddressLine2()
    {
        return $this->addressLine2;
    }

    /**
     * @param mixed $addressLine3
     *
     * @return PersonalDetails
     */
    public function setAddressLine3($addressLine3)
    {
        $this->addressLine3 = $addressLine3;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddressLine3()
    {
        return $this->addressLine3;
    }

    /**
     * @param mixed $town
     *
     * @return PersonalDetails
     */
    public function setTown($town)
    {
        $this->town = $town;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * @param mixed $dateOfBirth
     *
     * @return PersonalDetails
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @param mixed $drivingLicenceNumber
     *
     * @return PersonalDetails
     */
    public function setDrivingLicenceNumber($drivingLicenceNumber)
    {
        $this->drivingLicenceNumber = $drivingLicenceNumber;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDrivingLicenceNumber()
    {
        return $this->drivingLicenceNumber;
    }

    /**
     * @param mixed $drivingLicenceRegion
     *
     * @return PersonalDetails
     */
    public function setDrivingLicenceRegion($drivingLicenceRegion)
    {
        $this->drivingLicenceRegion = $drivingLicenceRegion;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDrivingLicenceRegion()
    {
        return $this->drivingLicenceRegion;
    }

    /**
     * @param mixed $email
     *
     * @return PersonalDetails
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $emailConfirmation
     *
     * @return PersonalDetails
     */
    public function setEmailConfirmation($emailConfirmation)
    {
        $this->emailConfirmation = $emailConfirmation;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmailConfirmation()
    {
        return $this->emailConfirmation;
    }

    /**
     * @param mixed $postcode
     *
     * @return PersonalDetails
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @param mixed $telephoneNumber
     *
     * @return PersonalDetails
     */
    public function setPhoneNumber($telephoneNumber)
    {
        $this->phoneNumber = $telephoneNumber;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @return array
     */
    private function extractDisplayableRoleNames()
    {
        $roles = $this->getDisplayableSystemRoles();
        $siteAndOrganisationRoles = $this->getSiteAndOrganisationRoles();
        foreach ($siteAndOrganisationRoles as $roleType => $organisationOrSiteRoles) {
            foreach ($organisationOrSiteRoles as $siteAndOrganisation) {
                foreach ($siteAndOrganisation['roles'] as $role) {
                    if (!in_array($role, $roles)) {
                        $roles[] = $role;
                    }
                }
            }
        }

        return $roles;
    }

    public function getRoles()
    {
        $roles = $this->getSystemRoles();
        $siteAndOrganisationRoles = $this->getSiteAndOrganisationRoles();
        foreach ($siteAndOrganisationRoles as $roleType => $organisationOrSiteRoles) {
            foreach ($organisationOrSiteRoles as $siteAndOrganisation) {
                foreach ($siteAndOrganisation['roles'] as $role) {
                    if (!in_array($role, $roles)) {
                        $roles[] = $role;
                    }
                }
            }
        }

        return $roles;
    }
}
