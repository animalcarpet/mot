<?php

namespace DvsaMotTestTest\Controller;

use DvsaCommonTest\Bootstrap;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;

/**
 * Superclass of controller tests for MOT-testing related controllers.
 *
 * All tests are run with a tester user logged in. This can be overridden on a case-by-case basis.
 */
abstract class AbstractDvsaMotTestTestCase extends AbstractFrontendControllerTestCase
{
    protected function setUp()
    {
        $this->setServiceManager(Bootstrap::getServiceManager());
        parent::setUp();
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asTester());
    }
}
