<?php

namespace OrganisationApi\Model\RoleRestriction;

use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use DvsaEntities\Entity\Person;
use OrganisationApi\Model\OrganisationPersonnel;

/**
 * Class AedRestriction.
 */
class AedRestriction extends AbstractOrganisationRoleRestriction
{
    const NOT_AE_ERROR = 'You cannot assign an AED to an organisation that is not an Authorised Examiner';

    /**
     * Checks if all requirements are met to assign AED role to the user in the given organisation.
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

        return $errors;
    }

    public function getRole()
    {
        return OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE;
    }
}
