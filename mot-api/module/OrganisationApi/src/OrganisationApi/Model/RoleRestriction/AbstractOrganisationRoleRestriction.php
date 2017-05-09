<?php

namespace OrganisationApi\Model\RoleRestriction;

use DvsaCommon\Model\DvsaRole;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use DvsaEntities\Entity\Person;
use OrganisationApi\Model\OrganisationPersonnel;
use OrganisationApi\Model\RoleRestrictionInterface;

abstract class AbstractOrganisationRoleRestriction implements RoleRestrictionInterface
{
    const DVSA_ROLE_OWNER_ERROR = 'You can not nominate a user with DVSA role for trade roles.';

    /** @var AuthorisationServiceInterface */
    private $authorisationService;

    public function __construct($authorisationService)
    {
        $this->authorisationService = $authorisationService;
    }

    /**
     * Checks if all requirements are met to assign a to the user in the given organisation.
     * Return unmet conditions.
     *
     * @param Person                $person
     * @param OrganisationPersonnel $personnel
     *
     * @return ErrorSchema
     */
    public function verify(Person $person, OrganisationPersonnel $personnel)
    {
        $errors = new ErrorSchema();

        $nomineeId = $person->getId();

        $personRoles = $this->authorisationService->getRolesAsArray($nomineeId);

        if (DvsaRole::containDvsaRole($personRoles)) {
            $errors->add(self::DVSA_ROLE_OWNER_ERROR);
        }

        return $errors;
    }

    /**
     * @return string The role this restriction applies to
     */
    abstract public function getRole();
}
