<?php

namespace SiteApi\Model\RoleRestriction;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Model\DvsaRole;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use DvsaEntities\Entity\Person;
use SiteApi\Model\RoleRestrictionInterface;
use SiteApi\Model\SitePersonnel;

/**
 * Class AbstractSiteRoleRestriction.
 */
abstract class AbstractSiteRoleRestriction implements RoleRestrictionInterface
{
    const NOT_VTS_RESTRICTION = '%s can only be assigned in a site that is a Vehicle Testing Station';

    const DVSA_ROLE_OWNER_ERROR = 'You can not nominate a user with DVSA role for trade roles.';

    /** @var MotAuthorisationServiceInterface */
    private $authorisationService;

    /**
     * @param MotAuthorisationServiceInterface $authorisationService
     */
    public function __construct(MotAuthorisationServiceInterface $authorisationService)
    {
        $this->authorisationService = $authorisationService;
    }

    /**
     * Checks if all requirements are met to assign a role to the user in the given organisation.
     * Return unmet conditions.
     *
     * @param Person        $person
     * @param SitePersonnel $personnel
     *
     * @return ErrorSchema
     */
    public function verify(Person $person, SitePersonnel $personnel)
    {
        $errors = new ErrorSchema();

        if (!$personnel->getSite()->isVehicleTestingStation()) {
            $error = sprintf(self::NOT_VTS_RESTRICTION, $this->getRole());
            $errors->add($error);
        }

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
