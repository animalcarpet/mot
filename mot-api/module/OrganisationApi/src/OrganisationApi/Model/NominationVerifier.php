<?php

namespace OrganisationApi\Model;

use DvsaEntities\Entity\OrganisationBusinessRoleMap;

/**
 * Verifies if the given nomination is possible and doesn't break nomination rules.
 *
 * Class NominationVerifier
 */
class NominationVerifier
{
    const ERROR_ALREADY_HAS_ROLE = 'This person already has this role';
    const ERROR_ALREADY_HAS_NOMINATION = 'This person has been already nominated to this role';

    private $roleAvailability;

    public function __construct(
        RoleAvailability $roleAvailability
    ) {
        $this->roleAvailability = $roleAvailability;
    }

    /**
     * @param OrganisationBusinessRoleMap $nomination
     *
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function verify(OrganisationBusinessRoleMap $nomination)
    {
        $unmetConditions = $this->roleAvailability->findUnmetRestrictions($nomination);
        $unmetConditions->throwIfAny();
    }
}
