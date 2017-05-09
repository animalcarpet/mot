<?php

namespace SiteApi\Model;

use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use SiteApi\Factory\SitePersonnelFactory;

/**
 * Verifies if the given nomination is possible and doesn't break nomination rules.
 *
 * Class NominationVerifier
 */
class NominationVerifier
{
    const ERROR_ALREADY_HAS_ROLE = 'This person already has this role';
    const ERROR_ALREADY_HAS_NOMINATION = 'This person has been already nominated to this role';

    private $roleRestrictionsSet;
    private $personnelFactory;

    public function __construct(
        RoleRestrictionsSet $roleRestrictionsSet,
        SitePersonnelFactory $personnelFactory
    ) {
        $this->roleRestrictionsSet = $roleRestrictionsSet;
        $this->personnelFactory = $personnelFactory;
    }

    /**
     * @param SiteBusinessRoleMap $nomination
     *
     * @return ErrorSchema
     */
    public function verify(SiteBusinessRoleMap $nomination)
    {
        $personnel = $this->personnelFactory->create($nomination->getSite());
        $existingPosition = $personnel->findPosition($nomination->getPerson(), $nomination->getSiteBusinessRole());

        if ($existingPosition) {
            return $this->getAlreadyExistsError($existingPosition);
        }

        $roleRestriction = $this->roleRestrictionsSet->getRestrictionForRole($nomination->getSiteBusinessRole());
        $unmetConditions = $roleRestriction->verify($nomination->getPerson(), $personnel);

        return $unmetConditions;
    }

    private function getAlreadyExistsError(SiteBusinessRoleMap $existingPosition)
    {
        $errors = new ErrorSchema();

        if ($existingPosition->getBusinessRoleStatus()->getCode() == BusinessRoleStatusCode::PENDING) {
            $errors->add(self::ERROR_ALREADY_HAS_NOMINATION);
        } else {
            $errors->add(self::ERROR_ALREADY_HAS_ROLE);
        }

        return $errors;
    }
}
