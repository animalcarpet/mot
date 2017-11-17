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
    const WRONG_GROUP = 'This person is not qualified to be a tester for this class of vehicle';

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
        } elseif (!$this->hasCorrectGroupQualification($person, $personnel)) {
            $errors->add(self::WRONG_GROUP);
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
                    ]
                );

                return $isAllowed;
            }
        );
    }

    /**
     * @param Person $person
     * @param SitePersonnel $sitePersonnel
     * @return bool
     */
    public function hasCorrectGroupQualification(Person $person, SitePersonnel $sitePersonnel)
    {
        $siteClasses = $sitePersonnel->getSite()->getApprovedVehicleClasses();
        foreach ($siteClasses as $class) {
            if($person->isQualifiedTesterForVehicleClass($class)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string The role this restriction applies to
     */
    public function getRole()
    {
        return SiteBusinessRoleCode::TESTER;
    }
}
