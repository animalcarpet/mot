<?php

namespace OrganisationApi\Model\RoleRestriction;

use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\Person;
use OrganisationApi\Model\OrganisationPersonnel;

/**
 * Class AedmRestriction.
 */
class AedmRestriction extends AbstractOrganisationRoleRestriction
{
    const NOT_AE_ERROR = 'You cannot assign an AEDM to an organisation that is not an Authorised Examiner';
    const SITE_ALREADY_HAS_AEDM = 'There is already an AEDM assigned to this Authorised Examiner';

    /**
     * @var array
     */
    private static $activeStatusCodes = [
        BusinessRoleStatusCode::ACCEPTED,
        BusinessRoleStatusCode::ACTIVE,
        BusinessRoleStatusCode::PENDING,
    ];

    /**
     * Checks if all requirements are met to assign AEDM role to the user in the given organisation.
     * Returns unmet conditions.
     *
     * @param Person                $person
     * @param OrganisationPersonnel $personnel
     *
     * @return ErrorSchema
     */
    public function verify(Person $person, OrganisationPersonnel $personnel)
    {
        $errors = parent::verify($person, $personnel);

        if (!$personnel->getOrganisation()->isAuthorisedExaminer()) {
            $errors->add(self::NOT_AE_ERROR);
        }

        if ($this->hasAedm($personnel)) {
            $errors->add(self::SITE_ALREADY_HAS_AEDM);
        }

        return $errors;
    }

    private function hasAedm(OrganisationPersonnel $personnel)
    {
        return ArrayUtils::anyMatch(
            $personnel->getPositions(), function (OrganisationBusinessRoleMap $position) {
                return in_array($position->getBusinessRoleStatus()->getCode(), self::$activeStatusCodes)
                    && $position->getOrganisationBusinessRole()->getName() == $this->getRole();
            }
        );
    }

    public function getRole()
    {
        return OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER;
    }
}
