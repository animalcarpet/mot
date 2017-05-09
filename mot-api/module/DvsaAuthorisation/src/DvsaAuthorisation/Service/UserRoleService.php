<?php

namespace DvsaAuthorisation\Service;

use DvsaCommon\Enum\OrganisationContactTypeCode;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\OrganisationContact;
use DvsaEntities\Entity\PersonSystemRoleMap;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use DvsaEntities\Entity\SiteContact;
use DvsaEntities\Repository\OrganisationBusinessRoleMapRepository;
use DvsaEntities\Repository\PersonSystemRoleMapRepository;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\SiteBusinessRoleMapRepository;

/**
 * List user roles.
 */
class UserRoleService
{
    private $organisationRoleMapRepository;
    private $siteRoleMapRepository;
    private $personSystemRoleMapRepository;

    /**
     * @param OrganisationBusinessRoleMapRepository $organisationRoleMapRepository
     * @param SiteBusinessRoleMapRepository         $siteRoleMapRepository
     * @param PersonSystemRoleMapRepository         $personSystemRoleMapRepository
     */
    public function __construct(
        $organisationRoleMapRepository,
        $siteRoleMapRepository,
        $personSystemRoleMapRepository
    ) {
        $this->organisationRoleMapRepository = $organisationRoleMapRepository;
        $this->siteRoleMapRepository = $siteRoleMapRepository;
        $this->personSystemRoleMapRepository = $personSystemRoleMapRepository;
    }

    /**
     * @param Person $person
     *
     * @return array [
     *               'system' => string[]
     *               'organisations' => [
     *               'name' => string,
     *               'address' => string,
     *               'roles' => string[],
     *               'sites' => ...
     *               ]
     */
    public function getDetailedRolesForPerson(Person $person)
    {
        return [
            'system' => $this->getActiveSystemRoles($person->getId()),
            'organisations' => $this->getActiveOrganisationRoles($person->getId()),
            'sites' => $this->getActiveSiteRoles($person->getId()),
        ];
    }

    public function getPendingRolesForPerson($personId)
    {
        return [
            'system' => $this->getPendingSystemRoles($personId),
            'organisations' => $this->getPendingOrganisationRoles($personId),
            'sites' => $this->getPendingSiteRoles($personId),
        ];
    }

    private function getActiveSystemRoles($personId)
    {
        $roles = $this->personSystemRoleMapRepository->getActiveUserRoles($personId);

        return $this->getSystemRoles($roles);
    }

    private function getPendingSystemRoles($personId)
    {
        $roles = $this->personSystemRoleMapRepository->getPendingUserRoles($personId);

        return $this->getSystemRoles($roles);
    }

    /**
     * @param PersonSystemRoleMap[] $roles
     *
     * @return array
     */
    private function getSystemRoles($roles)
    {
        $systemRoles = [];
        foreach ($roles as $role) {
            $systemRoles[] = $role->getPersonSystemRole()->getName();
        }

        return ['roles' => $systemRoles];
    }

    private function getActiveOrganisationRoles($personId)
    {
        /** @var OrganisationBusinessRoleMap[] $roles */
        $roles = $this->organisationRoleMapRepository->getActiveUserRoles($personId);

        return $this->getOrganisationRoles($roles);
    }

    private function getPendingOrganisationRoles($personId)
    {
        /** @var OrganisationBusinessRoleMap[] $roles */
        $roles = $this->organisationRoleMapRepository->getPendingUserRoles($personId);

        return $this->getOrganisationRoles($roles);
    }

    /**
     * @param OrganisationBusinessRoleMap[] $roles
     *
     * @return array
     */
    private function getOrganisationRoles($roles)
    {
        $organisationRoles = [];

        foreach ($roles as $role) {
            $organisation = $role->getOrganisation();
            if (!array_key_exists($organisation->getId(), $organisationRoles)) {
                $organisationRoles[$organisation->getId()] = [
                    'name' => $organisation->getName(),
                    'number' => $organisation->getAuthorisedExaminer()->getNumber(),
                    'address' => $this->mapOrganisationContactsToAddress($organisation->getContacts()),
                    'roles' => [],
                ];
            }

            $organisationRoles[$organisation->getId()]['roles'][]
                = $role->getOrganisationBusinessRole()->getShortName();
        }

        return $organisationRoles;
    }

    /**
     * @param $organisationContacts OrganisationContact[]
     *
     * @return string
     */
    private function mapOrganisationContactsToAddress($organisationContacts)
    {
        foreach ($organisationContacts as $contact) {
            if ($contact->getType()->getCode() === OrganisationContactTypeCode::REGISTERED_COMPANY) {
                return $this->getAddressAsString($contact->getDetails()->getAddress());
            }
        }

        return '';
    }

    /**
     * @param Address $address
     *
     * @return string
     */
    private function getAddressAsString($address)
    {
        return implode(
            ', ',
            array_filter(
                [
                    $address->getAddressLine1(),
                    $address->getAddressLine2(),
                    $address->getAddressLine3(),
                    $address->getAddressLine4(),
                    $address->getTown(),
                    $address->getPostcode(),
                ]
            )
        );
    }

    private function getActiveSiteRoles($personId)
    {
        /** @var SiteBusinessRoleMap[] $roles */
        $roles = $this->siteRoleMapRepository->getActiveUserRoles($personId);

        return $this->getSiteRoles($roles);
    }

    private function getPendingSiteRoles($personId)
    {
        /** @var SiteBusinessRoleMap[] $roles */
        $roles = $this->siteRoleMapRepository->getPendingUserRoles($personId);

        return $this->getSiteRoles($roles);
    }

    private function getSiteRoles($roles)
    {
        $siteRoles = [];

        foreach ($roles as $role) {
            $site = $role->getSite();
            if (!array_key_exists($site->getId(), $siteRoles)) {
                $siteRoles[$site->getId()] = [
                    'name' => $site->getName(),
                    'number' => $site->getSiteNumber(),
                    'address' => $this->mapSiteContactsToAddress($site->getContacts()),
                    'addressParts' => $this->mapSiteContactsToAddressParts($site->getContacts()),
                    'roles' => [],
                ];
            }

            $siteRoles[$site->getId()]['roles'][]
                = $role->getSiteBusinessRole()->getCode();
        }

        return $siteRoles;
    }

    /**
     * @param SiteContact[] $siteContacts
     *
     * @return \DvsaCommon\Dto\Contact\AddressDto|null
     */
    private function mapSiteContactsToAddress($siteContacts)
    {
        foreach ($siteContacts as $contact) {
            if ($contact->getType()->getCode() === SiteContactTypeCode::BUSINESS) {
                return $this->getAddressAsString($contact->getDetails()->getAddress());
            }
        }

        return '';
    }

    /**
     * @param SiteContact[] $siteContacts
     *
     * @return \DvsaCommon\Dto\Contact\AddressDto|null
     */
    private function mapSiteContactsToAddressParts($siteContacts)
    {
        foreach ($siteContacts as $contact) {
            if ($contact->getType()->getCode() === SiteContactTypeCode::BUSINESS) {
                $address = $contact->getDetails()->getAddress();

                return [
                    'addressLine1' => $address->getAddressLine1(),
                    'addressLine2' => $address->getAddressLine2(),
                    'addressLine3' => $address->getAddressLine3(),
                    'addressLine4' => $address->getAddressLine4(),
                    'town' => $address->getTown(),
                    'postcode' => $address->getPostcode(),
                ];
            }
        }

        return [];
    }
}
