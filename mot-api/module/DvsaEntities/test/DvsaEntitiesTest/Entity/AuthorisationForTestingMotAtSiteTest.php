<?php

namespace DvsaEntitiesTest\Entity;

use DvsaCommon\Enum\AuthorisationForTestingMotAtSiteStatusCode;
use DvsaEntities\Entity\AuthorisationForTestingMotAtSite;
use DvsaEntities\Entity\AuthorisationForTestingMotAtSiteStatus;
use PHPUnit_Framework_TestCase;

/**
 * Class AuthorisationForTestingMotAtSiteTest.
 */
class AuthorisationForTestingMotAtSiteTest extends PHPUnit_Framework_TestCase
{
    public function testIsApproved()
    {
        $authorisationForTestingMotAtSite = new AuthorisationForTestingMotAtSite();
        $authorisationForTestingMotAtSite->setStatus(
            new AuthorisationForTestingMotAtSiteStatus(AuthorisationForTestingMotAtSiteStatusCode::APPROVED)
        );

        $this->assertTrue($authorisationForTestingMotAtSite->isApproved());
    }
}
