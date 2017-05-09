<?php

namespace SiteApi\Model\RoleRestriction;

use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use DvsaEntities\Entity\AuthorisationForTestingMot;
use DvsaEntities\Entity\Person;
use SiteApi\Model\SitePersonnel;

/**
 * Class TesterRestriction.
 */
class TesterRestriction extends AbstractSiteRoleRestriction
{
    const NOT_QUALIFIED = 'This person is not qualified to be a tester';

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
        $errors = parent::verify($person, $personnel);

        if (!$this->isQualified($person)) {
            $errors->add(self::NOT_QUALIFIED);
        }

        return $errors;
    }

    public function isQualified(Person $person)
    {
        $authorisations = $person->getAuthorisationsForTestingMot();

        return ArrayUtils::anyMatch(
            $authorisations,
            function (AuthorisationForTestingMot $authorisation) {
                $code = $authorisation->getStatus()->getCode();
                $isAllowed = in_array(
                    $code,
                    [
                        AuthorisationForTestingMotStatusCode::QUALIFIED,
                        AuthorisationForTestingMotStatusCode::REFRESHER_NEEDED,
                        AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED,
                    ]
                );

                return $isAllowed;
            }
        );
    }

    /**
     * @return string The role this restriction applies to
     */
    public function getRole()
    {
        return SiteBusinessRoleCode::TESTER;
    }
}
