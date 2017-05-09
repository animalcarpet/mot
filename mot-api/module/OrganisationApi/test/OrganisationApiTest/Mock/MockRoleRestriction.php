<?php

namespace OrganisationApiTest\Mock;

use DvsaCommonApi\Service\Validator\ErrorSchema;
use DvsaEntities\Entity\Person;
use OrganisationApi\Model\OrganisationPersonnel;
use OrganisationApi\Model\RoleRestrictionInterface;

/**
 * Creating a mock as a separate class was faster and easier the using phpunit mocks.
 *
 * Class MockRoleRestriction
 */
class MockRoleRestriction implements RoleRestrictionInterface
{
    const ROLE_NAME = 'JANITOR';

    public function verify(Person $person, OrganisationPersonnel $personnel)
    {
        return new ErrorSchema();
    }

    /**
     * @return string The name of the role this restriction applies to
     */
    public function getRole()
    {
        return self::ROLE_NAME;
    }
}
